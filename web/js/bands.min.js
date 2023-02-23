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

			file.addEventListener('change', function()
			{
				if (this.file.files.length > 0)
  				{
  					this.label.innerHTML = "Logo: <span>" + this.file.files[0].name + "</span>";
  				}
  				else
  				{
  					this.label.innerHTML = "Logo";
  				}

			}.bind({ file:file, label:label }));

			uploadLink.addEventListener("click", function(e)
			{
				e.preventDefault();
				this.click();
			}.bind(file));

			deleteLink.addEventListener("click", function(e)
			{
				if(!confirm("Are you sure you want to delete this logo?"))
				{
					e.preventDefault();
					return false;
				}
				if(!BandPioneerBands.deleteLogo(this.dataset.logo))
				{
					e.preventDefault();
					return false;
				}
			})
		});
	}

}

BandPioneerBands.init();