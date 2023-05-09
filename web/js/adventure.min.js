class Adventure
{
	static RELATED_POSTS_URL = '/api/adventure-blog-search';

	static FILTER_TYPES = {
	    roles: "roles",
	    skill: "skill",
	    goals: "goals",
	};

	static LOAD_MESSAGES = [
		"Fetching custom harmonic data...",
		"Assembling your digital sound bytes...",
		"Streaming melodic sequences, please wait...",
		"Retrieving rhythmic patterns...",
		"Disecting musical algorithms...",
		"Please hold while we teach AI jazz theory...",
		"Sorry for the delay, AI is tuning its guitar...",
		"Composing binary symphonies...",
		"Patience is bitter, but its fruit is sweet...",
		"Rome wasn't built in a day. And neither was AI..."
	];

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

		if(this.hasCachedData())
		{
			this.loadContent();
		}
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

	hasCachedData()
	{
		return (localStorage.getItem(BandPioneer.ADVENTURE_FILTER_KEY) !== null);
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
	saveAIData(title, intro, goalResponses)
	{
		const obj = {
			intro: intro,
			title: title,
			goals: goalResponses
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

	resetUI()
	{
		document.querySelector('#role-articles > div').innerHTML = '';
		document.querySelector('#recent-articles').style.display = 'block';
		document.querySelector('.adventure-title').classList.remove('show');
		document.getElementById('intro-tips').innerHTML = '';
	}

	loadContent(reloadAI = false)
	{
		const obj = this;
		const cachedData = this.getCachedData();
		const roles = cachedData.filters.hasOwnProperty('filterRoles')
			? cachedData.filters.filterRoles.join(" and ")
			: "musician";

		// AI Intro Content

		if (!reloadAI && cachedData.ai.hasOwnProperty('title') && cachedData.ai.hasOwnProperty('intro'))
		{
			// load it from cache
			document.getElementById('intro-role').innerHTML = cachedData.ai.title;
			document.getElementById('intro-description').innerHTML = cachedData.ai.intro;

			obj.loadAITipsHtml(cachedData.ai.goals, roles);
		}
		else
		{
			this.resetUI();

			let introQuery = "I am a ";

			if (cachedData.filters.hasOwnProperty('filterSkill'))
			{
				introQuery += (cachedData.filters.filterSkill.join(" to ") + " ");
			}

			introQuery += (roles + " ");

			if (cachedData.filters.hasOwnProperty('filterGoals'))
			{
				introQuery += ("with goals to " + cachedData.filters.filterGoals.join(" and ") + ". ");
			}

			document.getElementById('intro-role').innerText = 'Creating Your Music Persona';
			document.getElementById('intro-description').innerHTML = '<h4>Something magical is happening!</h4>Through the power of BandPioneer and AI we are customizing this content, specifically for you...';
			document.querySelector('#intro-spinner svg').style.display = 'inline';

			(async () => {
				
				// load it from ai
			  	
			  	let titleResponse = await BandPioneer.aiQuery(`${introQuery} Create a job title for me using my roles or instruments. It must be less than 50 characters.`);
			  	let introResponse = await BandPioneer.aiQuery(`${introQuery} Describe my interests and goals in an intersting way in second person, and in no more than 1 paragraph.`);

			  	titleResponse = titleResponse.trim().replace(/\.$/, '');
			  	introResponse = introResponse.trim();

			  	document.querySelector('#intro-spinner svg').style.display = 'none';
				
				if(titleResponse !== "error" )
				{
					document.getElementById('intro-role').innerHTML = titleResponse.trim().replace(/\.$/, '');
				}
				if(introResponse !== "error" )
				{
					document.getElementById('intro-description').innerHTML = introResponse.trim();

					// obj.saveAIData(titleResponse, introResponse);
				}
				else
				{
					document.getElementById('intro-description').innerHTML = "<h4>We are unable to create your persona at this time.</h4>We apologize for the inconvenience, and will look into it promptly. Please try again later.";
				}

				obj.loadAITips(cachedData, roles, titleResponse, introResponse);
				
				obj.dirtyFilters = false;
			})();		
		}

		if(roles != 'musician')
		{
			this.loadRelatedPosts(Adventure.FILTER_TYPES.roles, roles, function(html)
			{
				if(html.trim().length === 0)
				{
					document.querySelector('#role-articles > div').innerHTML = '';
					document.querySelector('#recent-articles').style.display = 'block';
				}
				else
				{
					document.querySelector('#recent-articles').style.display = 'none';
					document.querySelector('#role-articles > div').innerHTML = html;
				}
			});
		}
		
	}

	loadAITipsHtml(goals, roles)
	{
		const obj = this;
		const verbs = ["How to", "Ways to", "How I can"];
		let random = Math.floor(Math.random() * verbs.length);
		let counter = 0;
		const tipsEle = document.getElementById('intro-tips');
		tipsEle.innerHTML = "";

		BandPioneer.each(goals, function()
		{
			random = ++random > verbs.length - 1 ? 0 :random;

			let rolesAry = roles.split(' and ');
		    if (rolesAry.length > 2)
		    {
		        let lastRole = rolesAry.pop();
		        roles = rolesAry.join(', ') + ' and ' + lastRole;
		    }

			const tipId = `tip-articles-${++counter}`;
			let tipsHtml = tipsEle.innerHTML;

			tipsHtml += `<section class="adventure-tips"><h3>${verbs[random]} ${this.goal}</h3><strong>As a ${roles}</strong><p>${this.tips}</p></section><div id="${tipId}"></div>`;
			tipsEle.innerHTML = tipsHtml;

			obj.loadRelatedPosts(Adventure.FILTER_TYPES.goals, this.goal, function(html)
			{
				document.getElementById(this.id).innerHTML = (html.trim().length === 0) ? '' : html;

				if(counter >= goals.length)
				{
					document.querySelector('.adventure-title').classList.add('show');
				}

			}.bind({id:tipId}));
		});
	}

	startTipsSpinner(start)
	{
		// LOAD_MESSAGES

		if(typeof(this.tipsy) === 'undefined')
		{
			this.tipsy = {
				spinning: false,
				spinner: document.querySelector('#tips-spinner'),
				svg: document.querySelector('#tips-spinner svg'),
				msg: document.querySelector('#tips-spinner strong'),
				loadMsg: function()
				{
					if(this.tipsy.spinning)
					{
						this.tipsy.spinner.classList.remove('show');
						setTimeout(function()
						{
							let random = Math.floor(Math.random() * Adventure.LOAD_MESSAGES.length);
							this.tipsy.msg.innerText = Adventure.LOAD_MESSAGES[random];
							this.tipsy.spinner.classList.add('show');
							setTimeout(function()
							{
								this.tipsy.loadMsg();
							}.bind(this), 5000);
						}.bind(this), 750);
					}
				}.bind(this)
			}
		}

		if(start)
		{
			this.tipsy.spinning = true;
			this.tipsy.loadMsg();
			this.tipsy.svg.style.display = 'inline';
			this.tipsy.spinner.style.display = 'block';
		}
		else
		{
			this.tipsy.spinning = false;
			this.tipsy.svg.style.display = 'none';
			this.tipsy.spinner.style.display = 'none';
		}
	}

	loadAITips(cachedData, roles, titleResponse, introResponse)
	{
		// AI Goal Tips
		const obj = this;
		let goalResponses = [];

		if (cachedData.filters.hasOwnProperty('filterGoals'))
		{
			const goals = cachedData.filters.filterGoals;

			if(goals.length > 0)
			{
				obj.startTipsSpinner(true);
				
				function getAIGoal(idx)
				{
					let goalQuery = `I am a ${roles}. Provide a few best tips for how I can ${goals[idx]}?`;

					obj.aiRequest(goalQuery, function(response)
					{
						goalResponses.push({ goal: this.goal, tips: response });

						if(++idx < goals.length)
						{
							getAIGoal(idx);
						}
						else
						{
							obj.startTipsSpinner(false);
							
							obj.loadAITipsHtml(goalResponses, roles);

							obj.saveAIData(titleResponse, introResponse, goalResponses);
						}
					}.bind({idx:idx, goal:goals[idx]}));
				}

				getAIGoal(0);
			}
		}
	}

	loadRelatedPosts(type, filters, callback)
	{
		if(type === Adventure.FILTER_TYPES.roles)
		{
			filters = filters.replace(/ and /g, "~");
		}

		const url = `${Adventure.RELATED_POSTS_URL}?type=${type}&filters=${filters}`;

		fetch(url).then((response) => {
		    if (response.ok)
		    {
		    	return response.text();
		    }
		}).then((html) => {
		    callback(html);
		}).catch((error) => {
		    console.error("Error:", error);
		});
	}

	filtersUpdated()
	{
		if(this.dirtyFilters)
		{
			this.loadContent(true);
		}
		else
		{
			bp.alert("No changes made. Please select a different role, skill level or goal to update your results.");
		}
	}

	/*
	 * Create events for filter inputs
	 */
	createFilters()
	{
		const obj = this;
		let filterLabels = document.querySelectorAll('#filters nav ul li label');
		const nextFilterBtn = document.querySelector("#filter-next");

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

			nextFilterBtn.addEventListener('click', function(e)
			{
				for (let i = 0; i < filterGroups.length; i++)
				{
					if(filterGroups[i].classList.contains('show'))
					{
						filterGroups[i].classList.remove('show');

						if(i === filterGroups.length - 1)
						{
							//Go
							categoryDescription.classList.remove('show');
							obj.filtersUpdated();
							break;
						}
						else if(i < filterGroups.length - 1)
						{
							//Next
							e.target.innerText = ((i + 1) === filterGroups.length - 1) ? "GO!" : "Next";
							filterGroups[i + 1].classList.add('show');
							break;
						}
					}
				}

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
						nextFilterBtn.innerText = (e.target.dataset.filter === '#filter-goals') ? "GO!" : "Next";

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
							if(target1 === checked[0])
								checked[1].checked = false;
							else
								checked[0].checked = false;

							// if(this.max > 1)
							// {
							// 	target1.checked = false;
							// }
							// else if(parseInt(this.max) === 1)
							// {
							// 	BandPioneer.each(this.inputs, function(idx, target2)
							// 	{
							// 		if(target1 !== target2)
							// 		{
							// 			target2.checked = false;
							// 		}
							// 	}.bind(this));
							// }
						}

						obj.saveFilterData();

					}.bind(this));
				}.bind({inputs: this.filterInputs, max: filterMax}));
			});
		}
	}
}