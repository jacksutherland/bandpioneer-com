class Adventure
{
	// static DATA_KEY = 'BandPioneer-adventure-data';

	constructor(initNewUX = false, maxRoles, maxSkills, maxGoals)
	{
		this.dirtyFilters = false;

		this.createFilters();

		// Load data stored in local storage

		const filtersLoaded = this.loadCachedFilters();

		// Since roles are checked in JS and TWIG, make sure the max is validated

		const checkedRoles = document.querySelectorAll('input[name="filter-roles"]:checked');
		if(checkedRoles.length > maxRoles)
		{	
			const idx = window.location.href.includes(checkedRoles[0].value) ? checkedRoles.length - 1 : 0;
			checkedRoles[idx].checked = false;
		}

		// Save data to local storage if starting a new adventure

		if(initNewUX && filtersLoaded)
		{
			this.saveFilterData();
		}

		// load dynamic UX data from local storage or AI

		this.loadAIContent();
	}

	/*
	 * Retrieve Adventure data statically: Adventure.data
	 */
	static get data()
	{
		return new Adventure().getCachedData();
	}

	/*
	 * Gets data from local storage
	 */
	getCachedData()
	{
		let obj = {
			filters: {},
			ai: {}
		};

		if(this.hasOwnProperty('filterData'))
		{
			obj.filters = this.filterData;
		}
		else
		{
			const storedFilters = localStorage.getItem(BandPioneer.ADVENTURE_FILTER_KEY);
			obj.filters = JSON.parse(storedFilters) ?? {};
		}

		if(this.hasOwnProperty('aiData'))
		{
			obj.ai = this.aiData;
		}
		else
		{
			const storedAIData = localStorage.getItem(BandPioneer.ADVENTURE_AI_KEY);
			obj.ai = JSON.parse(storedAIData) ?? {};
		}

		return obj;
	}

	/*
	 * Saves filter data to local storage
	 */
	saveFilterData()
	{
		const data = this.getDataObjFromUX();

		if (Object.keys(data).length === 0)
		{
			localStorage.removeItem(BandPioneer.ADVENTURE_FILTER_KEY);
			localStorage.removeItem(BandPioneer.ADVENTURE_AI_KEY);
		}
		else
		{
			localStorage.setItem(BandPioneer.ADVENTURE_FILTER_KEY, JSON.stringify(data));
		}
	}

	/*
	 * Saves AI data to local storage
	 */
	saveAIData(title, intro)
	{
		const obj = {
			intro: intro,
			title: title
		}

		localStorage.setItem(BandPioneer.ADVENTURE_AI_KEY, JSON.stringify(obj));
	}

	/*
	 * Returns a JS object from UX input selections
	 */
	getDataObjFromUX()
	{
		const inputs = document.querySelectorAll('input[name="filterRoles"], input[name="filterSkill"], input[name="filterGoals"]');

		const obj = Array.from(inputs).reduce((result, input) => {
			if(input.checked)
			{
				if (!result.hasOwnProperty(input.name))
				{
					result[input.name] = [];
				}
				result[input.name].push(input.value);
			}
			return result;
		}, {});

		return obj;
	}

	/*
	 * Loads data from local storage into the UX
	 */
	loadCachedFilters()
	{
		const filters = this.getCachedData().filters;

		if (Object.keys(filters).length > 0)
		{
			for (const key in filters)
			{
				if (filters.hasOwnProperty(key))
				{
					filters[key].forEach((value) => {
				
						const input = document.querySelector(`input[name="${key}"][value="${value}"]`);

						if (input)
						{
							input.checked = true;
						}
					});
				}
			}

			return true;
		}

		return false;
	}

	async aiRequest(query, callback)
	{
		const response = await BandPioneer.aiQuery(query);

		if(typeof(callback) === 'function')
		{
			callback(response);
		}
		else
		{
			return response;
		}
	}

	loadAIContent(reloadAI = false)
	{
		const obj = this;
		const cachedData = this.getCachedData();

		if (!reloadAI && cachedData.ai.hasOwnProperty('title') && cachedData.ai.hasOwnProperty('intro'))
		{
			// load it from cache
			document.getElementById('intro-role').innerHTML = cachedData.ai.title;
			document.getElementById('intro-description').innerHTML = cachedData.ai.intro;
		}
		else
		{
			let introQuery = "I am a ";
			let roles = "musician";
			let goals = [];

			if (cachedData.filters.hasOwnProperty('filterSkill'))
			{
				introQuery += (cachedData.filters.filterSkill.join(" to ") + " ");
			}

			if (cachedData.filters.hasOwnProperty('filterRoles'))
			{
				roles = cachedData.filters.filterRoles.join(" and ");
				
			}
			introQuery += (roles + " ");

			if (cachedData.filters.hasOwnProperty('filterGoals'))
			{
				goals = cachedData.filters.filterGoals;
				introQuery += ("with goals to " + goals.join(" and ") + ". ");
			}

			document.getElementById('intro-role').innerText = 'Creating Your Music Persona';
			document.getElementById('intro-description').innerHTML = '<h4>Something magical is happening!</h4>Through the power of BandPioneer and AI we are customizing this content, specifically for you...';
			document.querySelector('#intro-spinner svg').style.display = 'inline';

			(async () => {
				
				// load it from ai
			  	
			  	const titleResponse = await BandPioneer.aiQuery(`${introQuery} Create a job title for me using my roles or instruments. It must be less than 50 characters.`);
			  	const introResponse = await BandPioneer.aiQuery(`${introQuery} Describe my interests and goals in an intersting way in second person, and in no more than 1 paragraph.`);

				if(goals.length > 0)
				{
					function getAIGoal(idx)
					{
						let goalQuery = `I am a ${roles}. How can I ${goals[idx]}?`;

						obj.aiRequest(goalQuery, function(response)
						{
							let tipsHtml = document.getElementById('intro-tips').innerHTML;
							tipsHtml += `<h4>${this.goal}</h4><p>${response}</p>`;
							document.getElementById('intro-tips').innerHTML = tipsHtml;
							if(++idx < goals.length)
							{
								getAIGoal(idx);
							}
						}.bind({idx:idx, goal:goals[idx]}));
					}

					getAIGoal(0);
				}

			  	document.querySelector('#intro-spinner svg').style.display = 'none';
				
				if(titleResponse !== "error" )
				{
					document.getElementById('intro-role').innerHTML = titleResponse.trim();
				}
				if(introResponse !== "error" )
				{
					document.getElementById('intro-description').innerHTML = introResponse.trim();

					obj.saveAIData(titleResponse, introResponse);
				}
				else
				{
					document.getElementById('intro-description').innerHTML = "<h4>We are unable to create your persona at this time.</h4>We apologize for the inconvenience, and will look into it promptly. Please try again later.";
				}
				
				obj.dirtyFilters = false;
			})();		
		}
	}

	filtersUpdated()
	{
		if(this.dirtyFilters)
		{
			this.loadAIContent(true);
		}
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
				obj.filtersUpdated();

			}.bind(categoryDescription));

			BandPioneer.each(filterLabels, function()
			{
				this.addEventListener('click', function(e)
				{
					if(e.target.dataset.filter === 'load-data')
					{
						this.classList.remove('show');
						obj.filtersUpdated();
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

						obj.dirtyFilters = true;

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
						obj.saveFilterData();
					}.bind(this));
				}.bind({inputs: this.filterInputs, max: filterMax}));
			});
		}
	}
}