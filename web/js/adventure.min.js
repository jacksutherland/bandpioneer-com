class Adventure
{
	// static DATA_KEY = 'BandPioneer-adventure-data';

	constructor()
	{
		this.createFilters();

		this.loadUXDataObj();
	}

	/*
	 * Retrieve Adventure data statically: Adventure.data
	 */
	static get data()
	{
		return new Adventure().getData();
	}

	/*
	 * Gets data from local storage
	 */
	getData()
	{
		// const data = localStorage.getItem(Adventure.DATA_KEY);
		const data = localStorage.getItem(BandPioneer.ADVENTURE_DATA_KEY);
		return JSON.parse(data);
	}

	/*
	 * Saves data to local storage
	 */
	saveData()
	{
		const data = this.getUXDataObj();
		// localStorage.setItem(Adventure.DATA_KEY, JSON.stringify(data));
		localStorage.setItem(BandPioneer.ADVENTURE_DATA_KEY, JSON.stringify(data));
	}

	/*
	 * Returns a JS object from UX input selections
	 */
	getUXDataObj()
	{
		const inputs = document.querySelectorAll('input[name="filter-roles"], input[name="filter-skill"], input[name="filter-goals"]');

		const obj = Array.from(inputs).reduce((result, input) => {
			if (!result.hasOwnProperty(input.name))
			{
				result[input.name] = [];
			}
			if(input.checked)
			{
				result[input.name].push(input.value);
			}
			return result;
		}, {});

		return obj;
	}

	/*
	 * Loads data from local storage into the UX
	 */
	loadUXDataObj()
	{
		const obj = this.getData();

		if(obj !== null)
		{
			for (const key in obj)
			{
				if (obj.hasOwnProperty(key))
				{
					obj[key].forEach((value) => {
				
						const input = document.querySelector(`input[name="${key}"][value="${value}"]`);

						if (input)
						{
							input.checked = true;
						}
					});
				}
			}
		}
	}

	loadAI()
	{
		alert('load AI...');
	}

	/*
	 * Create events for filter inputs
	 */
	createFilters()
	{
		const obj = this;
		let filterLabels = document.querySelectorAll('#filters nav ul li label');

		if(filterLabels.length)
		{
			const categoryDescription = document.querySelector('#category-description');
			const filterGroups = document.querySelectorAll('.filter-group');

			document.querySelector("#close-filters").addEventListener('click', function(e)
			{
				e.preventDefault();
				this.classList.remove('show');

			}.bind(categoryDescription));

			BandPioneer.each(filterLabels, function()
			{
				this.addEventListener('click', function(e)
				{
					if(e.target.dataset.filter === 'load-data')
					{
						this.classList.remove('show');
						obj.loadAI();
					}
					else
					{
						BandPioneer.each(filterGroups, function()
						{
							this.classList.remove('show');
						});
						this.querySelector(e.target.dataset.filter).classList.add('show');
						this.classList.add('show');
					}
				}.bind(categoryDescription));

			});

			BandPioneer.each(filterGroups, function()
			{
				let filterInputContainer = this.querySelector('.filter-inputs');
				let filterMax = filterInputContainer.dataset.max;
				this.filterInputs = filterInputContainer.querySelectorAll('label input[type="checkbox"]');
				BandPioneer.each(this.filterInputs, function(idx, target1)
				{
					target1.addEventListener('change', function(e)
					{
						e.preventDefault();
						const checked = Array.from(this.inputs).filter(function(checkbox)
						{
								return checkbox.checked;
						});

						if(checked.length > this.max)
						{
							if(this.max > 1)
							{
								target1.checked = false;
							}
							else if(parseInt(this.max) === 1)
							{
								BandPioneer.each(this.inputs, function(idx, target2)
								{
									if(target1 !== target2)
									{
										target2.checked = false;
									}
								}.bind(this));
							}
						}
						obj.saveData();
					}.bind(this));
				}.bind({inputs: this.filterInputs, max: filterMax}));
			});
		}
	}
}