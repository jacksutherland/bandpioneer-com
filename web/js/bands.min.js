/**
 * Band Pioneer, LLC 2023
 * 
 * The primary JS file for the authenticated bands site
 */

const BandPioneerBands = {

	// Called at the bottom of this file
	init: function ()
	{
		this.addEventListeners();


	},

	openView: function(view)
	{
		switch(view)
		{
			case 'band':
				document.querySelector('[data-segment="band"]').click();
		}
	},

	deleteLogo: async function(logoId)
	{
		let data = { "id": logoId };
		data[window.csrfTokenName] = window.csrfTokenValue;

		const response = await fetch('/bands/delete-logo', {
		    method: 'POST',
		    headers: {
		        'Accept': 'application/json',
		        'Content-Type': 'application/json'
		    },
		    body: JSON.stringify(data)
		})
	   .then(response => response.json());

	   	return response.result == "success";
	},

	addEventListeners: function()
	{
		const epkNavItems = document.querySelectorAll('.tab-menu li label');
		BandPioneer.each(epkNavItems, function()
		{
			this.addEventListener("click", function(e)
			{
				BandPioneer.each(epkNavItems, function()
				{
					this.classList.remove('active');
					document.getElementById(this.dataset.tab).checked = false;
					
				});
				this.classList.add('active');
			});

			if(this.classList.contains('active'))
			{
				this.click();
			}
		});



		const checkboxGroups = document.querySelectorAll('.checkboxes');

		BandPioneer.each(checkboxGroups, function()
		{ 
			const checkboxes = this.querySelectorAll('input[type=checkbox]');
			let checkedCount = 0;
			
			BandPioneer.each(checkboxes, function()
			{
				if(this.checked) checkedCount++;

				this.addEventListener("change", function(e)
				{
					let name = this.getAttribute("name").split('[')[0];
					let checks = document.querySelectorAll('input[name*="' + name + '"]');
					let checked = document.querySelectorAll('input[name*="' + name + '"]:checked');

					BandPioneer.each(checks, function()
					{
						this.disabled = checked.length >= 5 ? !this.checked : false;
					});
				});
			});

			if(checkedCount >= 5)
			{
				BandPioneer.each(checkboxes, function()
				{
					this.disabled = !this.checked;
				});
			}
		});

		const editBtns = document.querySelectorAll('[data-edit]');

		BandPioneer.each(editBtns, function()
		{
			this.addEventListener("click", function(e)
			{
				e.preventDefault();

				const btn = this;
				const details = document.getElementById(this.dataset.view);
				const form = document.getElementById(this.dataset.edit);

				details.classList.toggle('show');
				form.classList.toggle('show');

				if(form.classList.contains("show"))
				{
					btn.text = "Cancel";
					btn.classList.add("alt-btn");
				}
				else
				{
					btn.text = "Edit";
					btn.classList.remove("alt-btn");
				}
			});
		});

		const fileUploads = document.querySelectorAll('.file-upload');

		BandPioneer.each(fileUploads, function()
		{
			const uploadLink = this.querySelector('a.upload-btn');
			const deleteLink = this.querySelector('a.delete-btn');
			const label = this.querySelector('label');
			const file = this.querySelector('input[type="file"]');
			const caption = this.querySelector('input[name="caption"]');
			const isForm = (this.nodeName === "FORM");

			file.addEventListener('change', function()
			{
				if(this.isForm)
				{
					let caption = prompt("Enter a caption you'd like displayed with this image", "");

					if(caption != null)
					{
						this.caption.value = caption;

						this.parent.submit();
					}
				}
				else if (this.file.files.length > 0)
  				{
  					this.label.innerHTML = this.label.dataset.name + ": <span>" + this.file.files[0].name + "</span>";
  				}
  				else
  				{
  					this.label.innerHTML = this.label.dataset.name;
  				}

			}.bind({ file:file, label:label, isForm: isForm, parent: this, caption: caption }));

			uploadLink.addEventListener("click", function(e)
			{
				e.preventDefault();
				this.click();
			}.bind(file));

			if(deleteLink)
			{
				deleteLink.addEventListener("click", function(e)
				{
					e.preventDefault();

					if(confirm(`Are you sure you want to delete this ${this.label.dataset.name.toLowerCase()}?`))
					{
						BandPioneerBands.deleteLogo(this.deleteLink.dataset.logo).then(function(deleted)
						{
							if(deleted)
							{
								window.location.reload();
							}
							else
							{
								alert("Error: We ran into a problem deleting this file");
							}
						});
					}
				}.bind({ deleteLink: deleteLink, label:label }))
			}
		});
	}

}

BandPioneerBands.init();