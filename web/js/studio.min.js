const aiFetch = window.fetch;

class Studio
{
	static RELATED_POSTS_URL = '/api/studio-blog-search';

	static SAVE_KEYWORD_URL = '/bands/save-keyword';

	static FILTER_TYPES = {
	    roles: "roles",
	    skill: "skill",
	    goals: "goals",
	};

	static LOAD_MESSAGES = [
		"Customizing your goals with harmonic data...",
		"Assembling digital sound bytes, specifically for you...",
		"Streaming melodic sequences, please wait...",
		"Creating custom tips with rhythmic patterns...",
		"Disecting your unique musical algorithm...",
		"Please hold while we teach AI jazz theory...",
		"Sorry for the delay, AI is tuning its guitar...",
		"Composing your custom binary symphonies...",
		"Patience is bitter, but its fruit is sweet...",
		"Rome wasn't built in a day, and apparently neither was AI..."
	];

	static KEYWORD_SUBTITLES = {
		first: ['Essential', 'Useful', 'Helpful', 'Valuable'],
		last: ['Tips', 'Information', 'Advice', 'Insights']
	};

	constructor(initialRole, maxRoles, maxSkills, maxGoals, goalValues, keyword)
	{
		if(typeof(keyword) === 'object')
		{
			this.loadKeyword(keyword);
			return;
		}

		const initialRoleSelected = initialRole.trim().length > 0;

		this.dirtyFilters = false;

		this.goalValues = goalValues;

		this.createFilters();

		if(initialRoleSelected)
		{
			// Initial Role Selected - Start Over

			this.dirtyFilters = true;

			// Remove role from url
			// If user copies/pastes it will reload every time

			history.replaceState({}, null, window.location.pathname);

			if(this.hasCachedAIData())
			{
				this.loadContent();
			}

			this.openMenu();
		}
		else if(this.hasCachedFilters())
		{
			// Initial Role NOT Selected - Has Previous Filters - Load Previous UX

			this.loadCachedFilters();

			if(this.hasCachedAIData())
			{
				this.loadContent();
			}
			else
			{
				this.openMenu();
			}
		}
		else
		{
			// Initial Role NOT Selected - No Previous Filters - Open Menu

			this.openMenu();
		}
	}

	/*
	 * Retrieve Studio data statically: Studio.data
	 */
	static get data()
	{
		return new Studio().getCachedData();
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
			const storedFilters = localStorage.getItem(BandPioneer.STUDIO_FILTER_KEY);
			obj.filters = JSON.parse(storedFilters) ?? {};
		}

		if(this.hasOwnProperty('aiData'))
		{
			obj.ai = this.aiData;
		}
		else
		{
			const storedAIData = localStorage.getItem(BandPioneer.STUDIO_AI_KEY);
			obj.ai = JSON.parse(storedAIData) ?? {};
		}

		return obj;
	}

	getCachedKeywordData()
	{
		const data = localStorage.getItem(BandPioneer.STUDIO_DYNAMIC_KEY);
		return JSON.parse(data) ?? null;
	}

	hasCachedFilters()
	{
		return (localStorage.getItem(BandPioneer.STUDIO_FILTER_KEY) !== null);
	}

	hasCachedAIData()
	{
		return (localStorage.getItem(BandPioneer.STUDIO_AI_KEY) !== null);
	}

	/*
	 * Saves filter data to local storage
	 */
	saveFilterData()
	{
		const data = this.getDataObjFromUX();

		if (Object.keys(data).length === 0)
		{
			this.deleteFilterData();
		}
		else
		{
			localStorage.setItem(BandPioneer.STUDIO_FILTER_KEY, JSON.stringify(data));
		}
	}

	deleteFilterData()
	{
		localStorage.removeItem(BandPioneer.STUDIO_FILTER_KEY);
		localStorage.removeItem(BandPioneer.STUDIO_AI_KEY);
	}

	/*
	 * Saves AI data to local storage
	 */
	saveAIData(title, intro, goalResponses = '')
	{
		const obj = {
			intro: intro,
			title: title,
			goals: goalResponses
		}

		localStorage.setItem(BandPioneer.STUDIO_AI_KEY, JSON.stringify(obj));
	}

	saveKeywordData(data)
	{
		// localStorage.setItem(BandPioneer.STUDIO_DYNAMIC_KEY, JSON.stringify(data));

		// subtitle description

		let csrfTokenName = document.querySelector('meta[name="csrf-token-name"]').getAttribute('content');
		let csrfTokenValue = document.querySelector('meta[name="csrf-token-value"]').getAttribute('content');
		let formData = new FormData();
		formData.append('path', data.path);
		formData.append('title', data.subtitle);
		formData.append('body', data.body);
		formData.append('description', data.description);

		formData.append(csrfTokenName, csrfTokenValue);

		aiFetch(Studio.SAVE_KEYWORD_URL, { method: 'POST', body: formData })
		.then(response => response.text())
		// .then(result => console.log('Success:', result))
		.catch((error) => console.error('Error:', error));
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
		document.querySelector('.studio-title').classList.remove('show');
		document.getElementById('intro-tips').innerHTML = '';
	}

	loadKeyword(keyObj)
	{
		const titleElement = document.querySelector('#intro-role');
		const bodyElement = document.querySelector('#intro-description');
		const articlesElement = document.querySelector('#recent-articles');
		const spinnerElement = document.querySelector('#intro-spinner svg');

		// Add Read More link under articles

		const recentArticles = articlesElement.querySelectorAll('article');
		if(recentArticles.length)
		{
			let readMoreLink = recentArticles[0].querySelector('.blog-info ul li:first-child a');
			articlesElement.innerHTML += this.getReadMoreLink(readMoreLink.href);
		}

		if(keyObj.subtitle.length > 0 && keyObj.body.length > 0)
		{
			// Load content from cache for this query

			bodyElement.classList.add('text-left');
			bodyElement.innerHTML = keyObj.body;
			titleElement.innerText = keyObj.subtitle;
		}
		else
		{
			// Fetch and load content from AI API for this query

			titleElement.innerText = 'Something magical is happening!';
			bodyElement.innerHTML = 'Through the power of BandPioneer and AI we are customizing this content, specifically for you...';
			spinnerElement.style.display = 'inline';

			const obj = this;

			const query = `Write a 5 to 15 paragraph essay about "${keyObj.title}". The content must be practical and extremely insightful, and written as a sequence of paragraphs without a title or section headers. Make the last sentence a compelling CTA to read the articles below this text to learn more. `;
			const descQuery = `In less than 155 characters, write a thought provoking description about quick tips on "${keyObj.title}". This needs to be SEO friendly and less than 155 characters for a description meta tag. No hashtags.`;

			(async () => {
				
				// load it from ai
		  	
			  	let [response, descResponse] = await Promise.all([BandPioneer.aiQuery(query), BandPioneer.aiQuery(descQuery)]);
			  	
			  	let random = Math.floor(Math.random() * Studio.KEYWORD_SUBTITLES.first.length);

			  	keyObj.subtitle = `${Studio.KEYWORD_SUBTITLES.first[random]} ${keyObj.query} ${Studio.KEYWORD_SUBTITLES.last[random]}`;

			  	titleElement.innerText = keyObj.subtitle;
			  	spinnerElement.style.display = 'none';

			  	if(descResponse === "error")
				{
					descResponse = "Music Industry Insights and Marketing Tips for Profitable Musicians";
				}
				
				if(response !== "error")
				{
					const lastSentence = response.split('.').filter((sentence) => sentence.trim() !== '').pop();

					response = response.replace(lastSentence, `<strong>${lastSentence}</strong>`);

					bodyElement.classList.add('text-left');
					bodyElement.innerHTML = response;
					keyObj.body = response;
					keyObj.description = descResponse;

					obj.saveKeywordData(keyObj);
				}
				else
				{
					bodyElement.innerHTML = "<h4>We are unable to create your persona at this time.</h4>We apologize for the inconvenience, and will look into it promptly. Please try again later.";
				}
			})();
		}
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
				
				if(titleResponse !== "error")
				{
					document.getElementById('intro-role').innerHTML = titleResponse.trim().replace(/\.$/, '');
				}
				if(introResponse !== "error")
				{
					document.getElementById('intro-description').innerHTML = introResponse.trim();
				}
				else
				{
					document.getElementById('intro-description').innerHTML = "<h4>We are unable to create your persona at this time.</h4>We apologize for the inconvenience, and will look into it promptly. Please try again later.";
				}

				const goalsLoaded = obj.loadAITips(cachedData, roles, titleResponse, introResponse);

				if(!goalsLoaded && titleResponse !== "error" && introResponse !== "error")
				{
					// Only intro was loaded, so save it

					obj.saveAIData(titleResponse, introResponse);
				}
				
				obj.dirtyFilters = false;
			})();		
		}

		if(roles != 'musician')
		{
			this.renderPrimaryArticles(roles);
		}
	}

	renderPrimaryArticles(query)
	{
		this.fetchRelatedPosts(Studio.FILTER_TYPES.roles, query, function(html)
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

			tipsHtml += `<section class="studio-tips"><h3>${verbs[random]} ${this.goal}</h3><strong>As a ${roles}</strong><p>${this.tips}</p></section><div id="${tipId}"></div>`;
			tipsEle.innerHTML = tipsHtml;

			obj.fetchRelatedPosts(Studio.FILTER_TYPES.goals, this.goal, function(html)
			{
				let readMoreUrl = '/advice';

				switch(this.goal)
				{
					case "make money with music":
						readMoreUrl = '/finance';
						break;
					case "market and promote my music":
					case "influence on social media":
						readMoreUrl = '/marketing';
						break;
				}

				const relatedHTML = `${html}${obj.getReadMoreLink(readMoreUrl)}`;

				document.getElementById(this.id).innerHTML = (html.trim().length === 0) ? '' : relatedHTML;

				if(counter >= goals.length)
				{
					document.querySelector('.studio-title').classList.add('show');
				}

			}.bind({id:tipId, goal:this.goal}));
		});
	}

	getReadMoreLink(url)
	{
		return `<div style="margin-top:40px;text-align:center;"><a class="btn" href="${url}">Read More Like This</a></div>`;
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
							let random = Math.floor(Math.random() * Studio.LOAD_MESSAGES.length);
							this.tipsy.msg.innerText = Studio.LOAD_MESSAGES[random];
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
					let goalQuery = `I am a ${roles}. Provide a couple tips for how I can ${goals[idx]}?`;

					obj.aiRequest(goalQuery, function(response)
					{
						if(response !== 'error')
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
						}
					}.bind({idx:idx, goal:goals[idx]}));
				}

				getAIGoal(0);
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	fetchRelatedPosts(type, filters, callback)
	{
		if(type === Studio.FILTER_TYPES.roles)
		{
			filters = filters.replace(/ and /g, "~");
		}

		const url = `${Studio.RELATED_POSTS_URL}?type=${type}&filters=${filters}`;

		aiFetch(url).then((response) => {
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

	goAction()
	{
		let areRolesChecked = document.querySelectorAll('input[name="filterRoles"]:checked').length > 0;
		let areGoalsChecked = document.querySelectorAll('input[name="filterGoals"]:checked').length > 0;

		function userAlert(msg)
		{
			if(typeof(bp) !== 'undefined')
			{
				bp.alert(msg, function()
				{
					this.openMenu();
				}.bind(this));
			}
			else alert(msg);
		}

		userAlert = userAlert.bind(this);

		if(!areRolesChecked || !areGoalsChecked)
		{
			userAlert("You must at least select 1 role and goal.");
		}
		else if(this.dirtyFilters || !this.hasCachedAIData())
		{
			this.loadContent(true);
		}
		else
		{
			userAlert("Select a different role, skill level<br>or goal to update your results.");
		}
	}

	openMenu()
	{
		BandPioneer.each(document.querySelectorAll('.filter-group'), function()
		{
			if(this.id === 'filter-roles') this.classList.add('show');
			else this.classList.remove('show');
		});

		document.querySelector('#category-description').classList.add('show');
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
							obj.goAction();
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
						obj.goAction();
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
						}

						obj.saveFilterData();

					}.bind(this));
				}.bind({inputs: this.filterInputs, max: filterMax}));
			});
		}
	}
}