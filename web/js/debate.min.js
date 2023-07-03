class AIDebate
{
	static MAX_RESPONSES = 10;

	static DEBATE_QUERY_URL = '/api/debate-query';

	static fetchResponse(url)
	{
		console.log(`fetchResponse: ${url}`);
	    return new Promise((resolve, reject) => {
	    	const xhr = new XMLHttpRequest();
			xhr.open('GET', url);
			xhr.responseType = 'text';
			xhr.onload = function() {
				console.log(`xhr.onload: ${this.status}`);
				if (this.status >= 200 && this.status < 300)
				{
					resolve(xhr.response);
				}
				else
				{
				 	reject({
				    	status: this.status,
				    	statusText: xhr.statusText
				  	});
				}
			};
			xhr.onerror = function()
			{
				reject({
				 	status: this.status,
				 	statusText: xhr.statusText
				});
			};
			xhr.send();
	    });
	 }

	constructor(debateFormat)
	{
		this.debate = {
			active: true,
			format: debateFormat
		};

		this.responseCount = 0;

		this.responses = document.getElementById('responses');

		this.spinner = document.getElementById('spinner');

		this.scrollPane = document.getElementsByClassName('scroll-pane')[0];

		this.activeRebuttal = "prop";

		this.autoScroll = true;

		this.initializeUX();

		this.initialResponse();
	}

	initializeUX()
	{
		this.scrollDown();

		document.getElementById('pause-debate').addEventListener('click', function(e)
		{
			e.preventDefault();
			this.pauseDebate(!this.debate.active);
		}.bind(this));

		let scrollPane = document.getElementsByClassName('scroll-pane')[0];
		scrollPane.onscroll = function()
		{
		    if (scrollPane.scrollTop + scrollPane.clientHeight >= scrollPane.scrollHeight)
		    {
		        this.autoScroll = true;
		    }
		    else
		    {
		    	this.autoScroll = false;
		    }
		}.bind(this);
	}

	scrollDown()
	{
		if(this.autoScroll)
		{
			this.scrollPane.scrollTop = this.scrollPane.scrollHeight;
		}
	}

	pauseDebate(pause)
	{
		this.debate.active = pause;

		var btn = document.getElementById('pause-debate');

		if(this.debate.active)
		{
			btn.innerText = 'Pause Debate';
			this.showSpinner(true, 'Resuming debate...')
			this.nextResponse();
		}
		else
		{
			btn.innerText = 'Continue Debate';
			this.showSpinner(null, 'Debate has been paused')
		}
	}

	showSpinner(show, debaterAction)
	{
		if(typeof(debaterAction) === "string" && debaterAction.length > 0)
		{
			this.spinner.getElementsByTagName('label')[0].innerText = debaterAction;
		}

		if(show === null) // falsy option to hide spinner
		{
			this.spinner.classList.add('hide-spinner');
		}
		else
		{
			this.spinner.classList.remove('hide-spinner');
		}

		if(show === null || show)
			this.spinner.classList.remove('hide');
		else
			this.spinner.classList.add('hide');

		setTimeout(function()
	    {
	    	this.scrollDown();
	    }.bind(this), 0);
	}

	getResponseLength()
	{
		return document.getElementById('response-length').value;
	}

	getResponseDelay()
	{
		return Number(document.getElementById('response-delay').value);
	}

	getResponseComplexity()
	{
		let selectId = `${this.activeRebuttal}-complexity`;
		let select = document.getElementById(selectId);
		let response = "using simple terms";
		switch(select.value)
		{
			case "child":
				response = "explaining it as if I were a 5 year old child, with silly kid examples";
				break;
			case "high-school":
				response = "explaining it on a high school level";
				break;
			case "phd":
				response = "using academic terms on a PhD-level";
				break;
		}
		return response;
	}

	initialResponse()
	{
		const format = this.debate.format;

		let query = `You are a ${format[this.activeRebuttal].title} in a debate on ${format.topic}. The debate resolution is: ${format.resolution} Write an opening argument, in ${this.getResponseLength()}, ${ this.getResponseComplexity() }, from this perspective: ${format[this.activeRebuttal].perspective}`;

		this.showSpinner(true, `${format[this.activeRebuttal].name} is preparing an opening statement...`)

		this.responseQuery(query, function()
		{
			document.getElementById('pause-debate').classList.remove('hide');
			this.nextResponse();
		}.bind(this));
	}

	finalResponse()
	{
		const format = this.debate.format;

		let query = `You are a ${format[this.activeRebuttal].title} in a debate on ${format.topic}. The debate resolution is: ${format.resolution} Write a final closing argument, saying goodbye, in ${this.getResponseLength()}, ${ this.getResponseComplexity() }, from this perspective: ${format[this.activeRebuttal].perspective}`;

		this.showSpinner(true, `${format[this.activeRebuttal].name} is preparing an opening statement...`)

		this.responseQuery(query, function()
		{
			this.debate.active = false;
			this.showSpinner(null, `Debate concluded: ${AIDebate.MAX_RESPONSES} response limit reached.`);
			document.getElementById('pause-debate').classList.add('hide');
		}.bind(this));
	}

	nextResponse()
	{
		if(this.responseCount >= AIDebate.MAX_RESPONSES)
		{
			this.finalResponse();
		}
		else if(this.debate.active)
		{
			// this.activeRebuttal = this.activeRebuttal === "prop" ? "opp" : "prop";

			let delay = this.getResponseDelay();

			let readingPause = delay > 5000 ? 2000 : 0;

			setTimeout(function()
			{
				if(this.debate.active)
				{
					let currentRebuttal = this.debate.format[this.activeRebuttal];
					let lastRebuttal = this.debate.format[this.activeRebuttal === "prop" ? "opp" : "prop"];
					this.showSpinner(true, `${currentRebuttal.name} is reading ${lastRebuttal.name}'s response...`);
				}
			}.bind(this), readingPause);

			setTimeout(function()
			{
				if(this.debate.active)
				{
					const format = this.debate.format;

					this.showSpinner(true, `${this.debate.format[this.activeRebuttal].name} is responding...`);

					let query = `Your response should be ${this.getResponseLength()}. You are a ${format[this.activeRebuttal].title} in a debate on ${format.topic}. Your perspective ${format[this.activeRebuttal].perspective}. From that perspective, ${ this.getResponseComplexity() }, respond to this argument: ${this.lastResponse}`;
					
					// console.log(query);

					this.responseQuery(query);
				}
			}.bind(this), (delay - readingPause));
		}
	}

	responseQuery(query, callback)
	{
		if(this.debate.active && this.responseCount <= AIDebate.MAX_RESPONSES)
		{
			const format = this.debate.format;

			let url = `${AIDebate.DEBATE_QUERY_URL}?dt=${new Date().getTime()}&q=${query}`;

			// fetch(url).then((response) => {
			AIDebate.fetchResponse(url).then((response) => {
			    // if (response.ok)
			    // {
			    	// return response.text();
			    	return response;
			    // }
			}).then((html) => {
				if(this.debate.active)
				{
					if (typeof html !== "undefined")
					{
						this.responseCount++;

						this.showSpinner(false);

						console.log('AIDebate html');
						console.log(html);

						// Remove <script>, <link>, <br>, and <base> tags from Ezoic injection
						let formattedHTML = html;
					    formattedHTML = formattedHTML.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
					    formattedHTML = formattedHTML.replace(/<link\b[^<]*(?:(?!<\/link>)<[^<]*)*<\/link>/gi, '');
					    formattedHTML = formattedHTML.replace(/<br>/gi, '');
					    formattedHTML = formattedHTML.replace(/<base\b[^<]*(?:(?!<\/base>)<[^<]*)*<\/base>/gi, '');

					    console.log('AIDebate formattedHTML');
					    console.log(formattedHTML);

						this.lastResponse = formattedHTML;
						formattedHTML = formattedHTML.replace(/\n/g, '<br>');

						let now = new Date();
						let formattedDate = `${now.getMonth() + 1}/${now.getDate()}/${now.getFullYear()} ${now.getHours() % 12 || 12}:${now.getMinutes().toString().padStart(2, '0')}${now.getHours() >= 12 ? 'pm' : 'am'}`;
					    
					    this.responses.innerHTML += `<div class="ai-response ${this.activeRebuttal}"><label>${format[this.activeRebuttal].name} (${format[this.activeRebuttal].title}) at ${formattedDate}</label>${formattedHTML}</div>`;

					    // Toggle the active speaker
					    this.activeRebuttal = this.activeRebuttal === "prop" ? "opp" : "prop";
					    
					    setTimeout(function()
					    {
					    	this.scrollDown();
					    }.bind(this), 0);
					}
				    if(typeof(callback) === "function")
				    {
				    	callback();
				    }
				    else
				    {
				    	this.nextResponse();
				    }
				}
			}).catch((error) => {
			    console.error("Error:", error);
			});
		}
	}
}