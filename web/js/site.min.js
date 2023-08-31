/**
 * Band Pioneer, LLC 2023
 * 
 * The primary JS file for the public website
 */

let BandPioneer = {

	STUDIO_FILTER_KEY: 'BandPioneer-3xp3d1t10n-filters',

	STUDIO_AI_KEY: 'BandPioneer-3xp3d1t10n-ai',

	STUDIO_DYNAMIC_KEY: 'BandPioneer-dyn@m1c-keywords',

	AI_URL: '/api/chat-query',

	each: function(elements, callback)
	{
		// Helper function to loop through a collection

		elements = BandPioneer.getCollection(elements);

		for(var i=0; i<elements.length; i++)
		{
			(callback.bind(elements[i]))(i, elements[i]);
		}
	},

	getCollection: function(obj)
	{
		// Makes sure elements are iterable

		return typeof(obj[Symbol.iterator]) === "function"
			? obj : [obj];
	},

	aiQuery: async function(searchQuery)
	{
		if(searchQuery.trim().length > 0)
		{
			const url = BandPioneer.AI_URL + "?q=" + searchQuery; // &limit=1000 for limiting character count

			try {
				const response = await fetch(url);
				if (response.ok)
				{
					// return await response.text();
					const html = await response.text();
			        const parser = new DOMParser();
			        const doc = parser.parseFromString(html, 'text/html');

			        console.log('response.ok -> html');
					console.log(html);

					console.log('response.ok -> doc');
					console.log(doc);

					return doc.body.innerText;
				}
				else
				{
					console.error('response');
					console.error(await response.text());
					throw new Error("Failed to fetch HTML");
					return "error";
				}
			}
			catch (error)
			{
				console.error("Error:", error);
				return "error";
			}
		}
		else
		{
			return "error";
		}
	},

	Site: class
	{
		// Primary Class for Website UX

		static hoveringOverCategory = false;

		constructor()
		{

			this.search = {
				ele: document.querySelector('#search'),
				icon: document.querySelector('#search svg'),
				input: document.querySelector('#search input')
			};

			this.addHeaderEvents();
			this.addMenuEvents();
			this.addSearchEvents();
			this.questionValidation();

			const data = JSON.parse(localStorage.getItem(BandPioneer.STUDIO_AI_KEY));
			if(data !== null)
			{
				const studioMenu = document.getElementById('studio-menu');
				if(studioMenu != null)
				{
					document.getElementById('studio-menu').classList.add('data-loaded');
				}
			}
		}

		isHTML(str)
		{
			return str.charAt(0) === '<';
			// const regex = /<([a-z]+)([^<]+)*(?:>(.*)<\/\1>|\s+\/>)/gi;
			// return regex.test(str);
		}

		signUp()
		{
			this.alert('<iframe src="https://cdn.forms-content.sg-form.com/dfbe0477-dfb9-11ed-a98c-c641367c2345"/>');
		}

		alert(message, onCloseCallback)
		{
			const overlay = document.createElement('div');
			overlay.classList.add('overlay');

			const messageIsHTML = this.isHTML(message);
			const maxWidth = (message.length > 100 || messageIsHTML) ? '600px' : '400px';
			const modal = document.createElement('div');
			modal.classList.add('modal');
			modal.style.cssText = 'max-width:' + maxWidth + ';';

			const closeButton = document.createElement('a');
			closeButton.classList.add('close-button');
			closeButton.innerHTML = 'x';
			closeButton.href = '/';
			closeButton.onclick = function(e)
			{
				e.preventDefault();
				document.body.removeChild(overlay);
				if(typeof(onCloseCallback) === 'function') onCloseCallback();
			};

			const messageElem = document.createElement(messageIsHTML ? 'div' : 'p');
			messageElem.innerHTML = message;

			overlay.onclick = function(event)
			{
				if (event.target === overlay)
				{
					document.body.removeChild(overlay);
					if(typeof(onCloseCallback) === 'function') onCloseCallback();
				}
			};

			modal.appendChild(closeButton);
			modal.appendChild(messageElem);
			overlay.appendChild(modal);
			document.body.appendChild(overlay);
		}

		questionValidation()
		{
			var form = document.getElementById("search-form");

			var obj = this;

			if(form == null) return false;

			const badQuestions = [
                "Hey, you didn't enter a question! Try again, and put some oomph into it!",
                "Did you know questions have words? Try to actually enter some this time!",
                "That question is shorter than a Ramones song. Elaborate, please!",
                "Is that a question? Because I sure don't see anything. Enter more characters, please!",
                "Nope! That's not a question. Try again, but with some letters and stuff.",
                "That's no question! Fool me once, shame on you. Fool me twice... Oh, just don't fool me again!",
                "Psst, the 'Question' field is lonely. Entering some words would make it VERY happy.",
            ];
            let usedMessages = [];
            function getBadQuestion()
            {
                if (badQuestions.length === usedMessages.length)
                {
                    usedMessages = [];
                }

                let randomIndex;
                
                do
                {
                    randomIndex = Math.floor(Math.random() * badQuestions.length);
                } while (usedMessages.includes(randomIndex));
                
                usedMessages.push(randomIndex);
                
                return badQuestions[randomIndex];
            }

			form.addEventListener('submit', function(e)
			{
				if(this.q.value.trim().length <= 2)
				{
					obj.alert(getBadQuestion());
					e.preventDefault();
				}
			});
		}

		showSearch(show)
		{
			if(show)
			{
				this.search.input.value = "";
				this.search.ele.classList.add('show');
				this.search.input.focus();
			}
			else
			{
				this.search.ele.classList.remove('show');
			}
		}

		addHeaderEvents()
		{
			const pageHeader = document.querySelector('.page-header');

			if(pageHeader)
			{
				const header = document.querySelector('header');

				if(!header.classList.contains('fixed'))
				{
					const observer = new window.IntersectionObserver((entries) => {

						if (!entries[0].isIntersecting)
						{
							header.classList.add('sticky');
						}
						else
						{
							header.classList.remove('sticky');
						}
					});

					observer.observe(pageHeader);
				}
			}
		}

		addMenuEvents()
		{
			let menu = document.querySelector('#main-menu');
			let childMenus = menu.querySelectorAll('.dropdown-menu');
			let open = document.querySelector('#open-menu');
			let close = document.querySelector('#close-menu');
			let categoryMenu = document.querySelector('#category-menu');
			let categories = document.querySelectorAll('#category-menu nav ul li a');

			BandPioneer.each(childMenus, function()
			{
				this.querySelector('a').addEventListener('click', function(e)
				{
					e.preventDefault();

					let dropdown = this.nextElementSibling;

					let dropdownListener = function(e)
					{
						if(e.target === dropdown && e.type === 'click')
						{
							return false;
						}

						this.classList.remove('show');
						this.removeEventListener('mouseleave', dropdownListener);
						document.body.removeEventListener('click', dropdownListener, true); 
					}.bind(dropdown);

					if(dropdown.classList.contains('show'))
					{
						dropdown.classList.remove('show');
						dropdown.removeEventListener('mouseleave', dropdownListener);
						document.body.removeEventListener('click', dropdownListener, true); 
					}
					else
					{
						dropdown.classList.add('show');
						dropdown.addEventListener('mouseleave', dropdownListener);
						document.body.addEventListener('click', dropdownListener, true); 
					}
				});
			});

			open.addEventListener('click', function(e)
			{
				e.preventDefault();
				menu.classList.add('show');
			});

			close.addEventListener('click', function(e)
			{
				e.preventDefault();
				menu.classList.remove('show');
			});

			if(categoryMenu)
			{
				categoryMenu.addEventListener('mouseleave', function(e)
				{
					e.preventDefault();

					BandPioneer.categoryId = 0;
					BandPioneer.hoveringOverCategory = false;
					clearTimeout(BandPioneer.categoryTimeout);

					document.querySelector('#category-description').classList.remove('show');
				});
			}

			BandPioneer.each(categories, function()
			{
				this.addEventListener('mouseover', function(e)
				{
					e.preventDefault();

					BandPioneer.hoveringOverCategory = true;
					BandPioneer.categoryId = this.dataset.id;

					let categoryDescription = document.querySelector('#category-description');
					categoryDescription.querySelector('.container').innerHTML = this.dataset.description;

					BandPioneer.categoryTimeout = setTimeout(function()
					{
						if(BandPioneer.hoveringOverCategory && this.catId == BandPioneer.categoryId)
						{
							this.catDesc.classList.add('show');
						}
					}.bind({ catDesc: categoryDescription, catId: this.dataset.id }), 500);
				});
			});
		}

		addSearchEvents()
		{
			const searchInput = document.querySelector('input[name="q"]');

			if (searchInput == null) return false;

			// this is needed to avoid a refresh bug, but not sure why?!?!??
			searchInput.addEventListener('click', function(e)
			{
				e.preventDefault();
			});

			this.search.icon.addEventListener('click', function(e)
			{
				e.preventDefault();

				// console.log('search click');

				if(this.search.ele.classList.contains('show'))
				{
					this.showSearch(false);
				}
				else
				{
					this.showSearch(true);
				}
			}.bind(this));

			this.search.input.addEventListener('blur', function(e)
			{
				// console.log('search blur');
				this.showSearch(false);
			}.bind(this));

			this.search.input.addEventListener('keyup', function(e)
			{
				// console.log('search keyup');
				e = e || window.event;
			    if (e.keyCode == 27 || e.keyCode == 13)
			    {
			        this.showSearch(false);
			    }
			}.bind(this));
		}

		blogPost()
		{
			this.readingProgress();

			this.toc();

			window.onhashchange = function()
			{
				window.history.replaceState(null, null, window.location.pathname);
			};
		}

		toc()
		{
			const blogHeader = document.querySelector('.blog-header');
			const blogComments = document.querySelector('.blog-comments');
			const blogRelated = document.querySelector('.related-content');
			const blogContainer = document.querySelector('.blog-container');
			const toc = document.querySelector('.table-of-contents');

			let commentsVisible = false;
			let relatedVisible = false;

			const observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					if (entry.target === blogHeader)
					{
						if (entry.isIntersecting)
						{
							blogContainer.classList.remove('sticky');
						}
						else
						{
							blogContainer.classList.add('sticky');
						}
					}
					else if (entry.target === blogComments || entry.target === blogRelated)
					{
						if (entry.isIntersecting)
						{
							toc.classList.add('fadeaway');

							if (entry.target === blogComments)
							{
								commentsVisible = true;
							}
							else if (entry.target === blogRelated)
							{
								relatedVisible = true;
							}
						}
						else
						{
							if (entry.target === blogComments)
							{
								commentsVisible = false;
							}
							else if (entry.target === blogRelated)
							{
								relatedVisible = false;
							}

							if(!commentsVisible && !relatedVisible)
							{
								toc.classList.remove('fadeaway');
							}
						}
					}
				});
			}, { threshold: 0, rootMargin: `-50px 0px 0px 0px` });

			observer.observe(blogHeader);
			observer.observe(blogComments);
			observer.observe(blogRelated);
		}

		readingProgress()
		{
			const progress = document.getElementById('reading-progress');

			if(progress)
			{
		    	const lastArticleSection = Array.from(
					  document.querySelectorAll('.section-title')
					).pop();

				const readableHeight = lastArticleSection.offsetTop + lastArticleSection.offsetHeight + 100;

		    	function getScrollPercent()
		    	{
				    const h = document.documentElement, 
				          b = document.body,
				          st = 'scrollTop';

				    const pct = Math.floor((h[st]||b[st]) / (readableHeight - h.clientHeight) * 100);

				    return pct > 100 ? 100 : pct;
				}

				function setReadingProgress()
				{
					let pct = getScrollPercent() + '%';
					progress.style.width = pct;
					progress.parentElement.title = 'Reading Progress: ' + pct;
				}

		    	window.addEventListener("scroll", () => {
		      		setReadingProgress();
		    	});

		    	setReadingProgress();
		    }
		}

		createBandCarousels()
		{
			let bands = document.querySelectorAll('.band-carousel');

			BandPioneer.each(bands, function(idx, ele)
			{
				new BandPioneer.BandCarousel(ele);

			}.bind(this));
		}

		loginForm(formId)
		{
			document.getElementById(formId).addEventListener('submit', function(e)
			{
				const password = e.target.password;
				const confirmPassword = e.target.confirmPassword;
				const confirmPasswordError = document.getElementById('confirm-password-error');

				if(confirmPasswordError)
				{
					confirmPasswordError.remove();
				}

				if (password.value !== confirmPassword.value)
				{
					e.preventDefault();
					confirmPassword.insertAdjacentHTML('afterend', '<div id="confirm-password-error" class="errors" style="margin: -25px 0 20px 0;">Passwords do not match.</div>');
				}
			});
		}

		loadSearchResponse(searchQuery, numberOfResults)
		{
			const minDelay = 1000;
			const maxDelay = 3500;

			this.aiSearchQuery(searchQuery, numberOfResults);

			// Slightly delay search results so AI response doesn't seem so slow.
			setTimeout(function()
			{
				let related = document.getElementById("related-articles");
				let loading = document.getElementById("search-loading");

				loading.remove();
				if(related != null)
				{
					related.classList.add('loaded');
				}
			}, (Math.floor(Math.random() * (maxDelay - minDelay + 1)) + minDelay));
		}

		aiSearchQuery(searchQuery, numberOfResults)
		{
			if(searchQuery.trim().length > 0)
			{
				const url = BandPioneer.AI_URL + "?limit=1000&html=true&results=" + numberOfResults + "&q=" + searchQuery;
				const responseContainer = document.getElementById("ai-response");

				fetch(url).then((response) => {
				    if (response.ok)
				    {
				    	return response.text();
				    }
				    else
				    {
				    	throw new Error("Failed to fetch HTML");
				    }
				}).then((html) => {
				    responseContainer.innerHTML = html;
				    responseContainer.classList.add('loaded');
				}).catch((error) => {
				    console.error("Error:", error);
				});
			}
		}
	},

	BandCarousel: class
	{
		constructor(carousel)
		{
			this.scrollSpeed = 0.3;

			this.direction = 'left';

			this.slider = carousel.querySelector('.band-slider');

			this.slides = this.slider.querySelectorAll('.slide');

			this.carouselAnimationRequest = null;

			this.carouselAnimationFrame = null;

			// Randomly rotate images

			BandPioneer.each(this.slides, function(idx, slide)
			{
				let min = -4, max = 4;
				let deg = Math.random() * (max - min) + min;
				slide.style.transform = 'rotate(' + deg + 'deg)';

			}.bind(this));

			this.addEventListeners();

			this.createAnimationFrame();

		}

		addEventListeners()
		{
			this.resize = function()
			{
				this.slides = this.slider.querySelectorAll('.slide');
				let sliderWid = 0;

				BandPioneer.each(this.slides, function(idx, slide)
				{
					sliderWid += slide.getBoundingClientRect().width;
				});

				this.slider.style.width = (sliderWid + 50) + 'px';

			}.bind(this);

			window.addEventListener('resize', function()
			{
				this.resize();
			}.bind(this));

			let startMouseDragEvent = function(event)
			{
				let shiftX = (typeof(event.clientX) === 'undefined')
					? event.touches[0].clientX - this.slider.getBoundingClientRect().x
					: event.clientX - this.slider.getBoundingClientRect().x;

				this.stopAnimation();

				let mouseMoveEvent = function(event)
				{
					let pageX = (typeof(event.pageX) === 'undefined') ? event.touches[0].pageX : event.pageX;

					if(pageX < 0 || pageX > window.innerWidth)
					{
						return false;
					}

					let xPos = pageX - this.slider.offsetWidth / 2 + 'px';
					let box = this.slider.getBoundingClientRect();

					this.slider.style.transform = 'translateX(' + (pageX - shiftX) + 'px)';

				}.bind(this);

				let mouseUpEvent = function()
				{
					document.removeEventListener('mousemove', mouseMoveEvent);
					document.removeEventListener('touchmove', mouseMoveEvent);
					document.removeEventListener('mouseup', mouseUpEvent);
					document.removeEventListener('touchend', mouseUpEvent);

					this.slider.onmouseup = null;
					this.startAnimation();
				}.bind(this);

				document.addEventListener('mousemove', mouseMoveEvent);
				document.addEventListener('touchmove', mouseMoveEvent);
				document.addEventListener('mouseup', mouseUpEvent);
				document.addEventListener('touchend', mouseUpEvent);

			}.bind(this);

			this.slider.addEventListener('mousedown', startMouseDragEvent);
			this.slider.addEventListener('touchstart', startMouseDragEvent);

			this.slider.ondragstart = function()
			{
				return false;
			};

			this.resize();
		}

		startAnimation = function()
		{
			this.carouselAnimationRequest = requestAnimationFrame(this.carouselAnimationFrame);
		}

		stopAnimation = function()
		{
			cancelAnimationFrame(this.carouselAnimationRequest);
			this.carouselAnimationRequest = null;
		}

		createAnimationFrame()
		{
			this.carouselAnimationFrame = function()
			{
				// Occasionally resize to make sure width & coordinate value are still accurate

				if (this.carouselAnimationRequest % 1000 === 0) 
				{
					this.resize();
			        // console.log(this.carouselAnimationRequest +' iterations have passed')
			    }

				let box = this.slider.getBoundingClientRect();

				// console.log('carouselAnimationFrame ' + box.width  + ' < ' + window.innerWidth);

				if(box.width < window.innerWidth)
				{
					// Carousel is narrower than screen

					// console.log('carouselAnimationFrame narrower');

					if(box.x <= 0)
					{
						this.direction = 'right';
					}
					else if((box.width + box.x) > window.innerWidth)
					{
						this.direction = 'left';
					}
				}
				else
				{
					// Carousel is wider than screen

					// console.log('carouselAnimationFrame wider');

					if(box.x >= 0)
					{
						this.direction = 'left';
					} 
					else if(box.width - (-1 * box.x) < window.innerWidth)
					{
						this.direction = 'right';
					}
				}

				this.slider.style.transform = 'translateX(' + (this.direction === 'left' ? (box.x - this.scrollSpeed) : (box.x + this.scrollSpeed)) + 'px)';

				if(this.carouselAnimationRequest !== null)
				{
					this.startAnimation();
				}
			}.bind(this);

			this.startAnimation();
		}
	}
}