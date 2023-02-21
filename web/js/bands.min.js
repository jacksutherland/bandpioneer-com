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

	addEventListeners: function()
	{
		const editBtns = document.querySelectorAll('[data-edit]');

		BandPioneer.each(editBtns, function()
		{
			this.addEventListener("click", function(e)
			{
				e.preventDefault();

				const btn = this;
				const form = document.getElementById(this.dataset.edit);

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
	}

}

BandPioneerBands.init();