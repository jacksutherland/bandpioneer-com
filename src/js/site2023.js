/**
 * Band Pioneer, LLC - October 2023
 * 
 * The primary JS file for the public website
 */

class BandPioneerUX
{
	static stickyListeners = [];

	constructor()
	{
		this.addHeaderEvents();
		this.addTopicMenuEvents();
		this.startAnimations();

		// Delay the loading of ads, to improve performance scoring
		window.addEventListener('load', function()
		{
			setTimeout(function()
			{
			    var mvscript = document.createElement('script');
			    mvscript.type = 'text/javascript';
			    mvscript.src = "//scripts.mediavine.com/tags/band-pioneer.js";
			    mvscript.dataset.noptimize = "1";
			    mvscript.dataset.cfasync = "false";
			    mvscript.async = true;
			    document.head.appendChild(mvscript);
		    }, 4000);
		});
	}

	static getBreakpoint()
	{
		if (window.matchMedia('(max-width: 576px)').matches)
			return "sm";
		else if (window.matchMedia('(max-width: 768px)').matches)
			return "md";
		else if (window.matchMedia('(max-width: 992px)').matches)
			return "lg";
		else if (window.matchMedia('(max-width: 1200px)').matches)
			return "xl";
		else
			return "xxl";
	}

	static addStickyListener(fn)
	{
		BandPioneerUX.stickyListeners.push(fn);
	}

	static callStickyListeners(isSticky)
	{
	    BandPioneerUX.stickyListeners.forEach((fn) => fn(isSticky));
	}

	login(modalMessage)
	{
		if(typeof(modalMessage) === 'undefined')
		{
			modalMessage = 'Sign in to Band Pioneer';
		}

		if(!this.openModal('#login-modal', null, modalMessage))
		{
			window.location.href = "/account";
		}

		return false;
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

	openModal(selector, onCloseCallback, modalMessage)
	{
		const overlay = document.querySelector(selector);

		if(!overlay)
		{
			return false;
		}

		const modal = overlay.querySelector('.modal');
		const closeButton = modal.querySelector('.close-button') || modal.querySelector('.close-icon-button');

		if(typeof(modalMessage) === 'string')
		{
			let h3 = modal.querySelector('h3');
			if(h3)
			{
				h3.innerText = modalMessage;
			}
		}

		closeButton.onclick = function(e)
		{
			e.preventDefault();

			if(typeof(onCloseCallback) === 'function') onCloseCallback();
			overlay.classList.remove('show');
		};

		overlay.onclick = function(e)
		{
			if(!e.target.classList.contains('login-provider'))
			{
				e.preventDefault();
			}

			if (e.target === overlay)
			{
				if(typeof(onCloseCallback) === 'function') onCloseCallback();
				overlay.classList.remove('show');
			}
		};

		overlay.classList.add('show');

		return true;
	}

	isHTML(str)
	{
		return str.charAt(0) === '<';
	}

	addHeaderEvents()
	{
		/***** SEARCH DROPDOWN *****/

		function hoverOffSearchHandler(e)
		{
			document.getElementById('search-toggle').checked = false;
			document.querySelector('.search-dropdown').removeEventListener('mouseleave', hoverOffSearchHandler);
		}

		document.getElementById('search-toggle').addEventListener('change', function()
		{
			if(this.checked)
			{
				document.getElementById('search-input').focus();
				document.querySelector('.search-dropdown').addEventListener('mouseleave', hoverOffSearchHandler);
			}
		});

		/***** MOBILE MENU *****/

		const closeNav = function(e)
		{
			if (e.target.classList.contains('dropdown'))
			{
				return;
			}

			document.querySelectorAll('header nav')[0].classList.remove('show');

			document.querySelectorAll('header nav ul.show').forEach(ul => {
				ul.classList.remove('show');
			});

			document.removeEventListener('click', closeNav);
		}

		document.getElementById('open-menu').addEventListener('click', function(e)
		{
			e.preventDefault();
			e.stopPropagation();

			const navVisible = document.querySelectorAll('header nav')[0].classList.toggle('show');

			if(navVisible)
			{
				document.addEventListener('click', closeNav);
			}
			else
			{
				document.removeEventListener('click', closeNav);
			}
		});


		/***** DROPDOWN MENUS *****/

		const dropdownLinks = document.querySelectorAll('header nav ul a.dropdown');

		const closeDropdown = function(e)
		{
			document.querySelectorAll('header nav ul.show').forEach(ul => {
				if (!ul.contains(e.target) && !e.target.classList.contains('dropdown'))
				{
					ul.classList.remove('show');
					document.removeEventListener('click', closeDropdown);
				}
			});
		}

		dropdownLinks.forEach(link => {
			link.addEventListener('click', function(e)
			{
				e.preventDefault();

				const targetUL = this.nextElementSibling;
			  	const targetVisible = targetUL.classList.toggle('show');

			  	if(targetVisible)
			  	{
			  		document.addEventListener('click', closeDropdown);
			  	}
			  	else
			  	{
			  		document.removeEventListener('click', closeDropdown);
			  	}

			  	document.querySelectorAll('header nav ul.show').forEach(ul => {
					if (ul !== targetUL)
					{
						ul.classList.remove('show');
					}
				});
			});
		});


		/***** STICKY MENUS *****/

		const tracker = document.querySelector('.sticky-tracker');
		if(tracker)
		{
			const body = document.querySelector('body');
			
			const observer = new window.IntersectionObserver((entries) => {

				if(entries[0].boundingClientRect.top < 250)
				{
					if (!entries[0].isIntersecting)
					{
						body.classList.add('sticky');
						BandPioneerUX.callStickyListeners(true);
					}
					else
					{
						body.classList.remove('sticky');
						BandPioneerUX.callStickyListeners(false);
					}
				}
			}, { rootMargin: `-150px 0px 0px 0px` });

			observer.observe(tracker);
		}
	}

	addTopicMenuEvents()
	{
		document.querySelectorAll('[data-popup]').forEach(pop => {

			var bobj = {obj:this, pop:pop};

			pop.hovering = false;
			this.popoutDuration = Date.now();

			pop.addEventListener('mouseover', function(e)
			{
				const popinDuration = Date.now();
				const popDiff = popinDuration - this.obj.popoutDuration;
				const duration = popDiff < 500 ? 0 : 1000;

				this.pop.hovering = true;
				this.obj.popTimer = setTimeout(function()
				{
					if(this.pop.hovering)
					{
						document.getElementById(this.pop.dataset.popup).classList.add('show');
					}
				}.bind(this), duration);
			}.bind(bobj));

			pop.addEventListener('mouseout', function(e)
			{
				clearTimeout(this.obj.popTimer);
				document.getElementById(this.pop.dataset.popup).classList.remove('show');
				this.pop.hovering = false;
				this.obj.popoutDuration = Date.now();
			}.bind(bobj));

		});
	}

	startAnimations()
	{
		// Homepage Animations

		// return false;

		var ctaText = document.getElementById('cta-text');
		var ctaImg = document.getElementById('cta-image');
		var ctaInterval = null;

		if(ctaText && ctaImg)
		{
			const ctaMin = 1, ctaMax = parseInt(ctaImg.dataset.max);
			var ctaIdx = parseInt(ctaImg.dataset.idx);
			var preloadCounter = 1;

			const ctaImgRotator = function()
			{
				ctaIdx = (ctaIdx === ctaMax) ? 1 : ctaIdx + 1;

				ctaImg.classList.add('blur-out');

				setTimeout(function()
				{
					ctaImg.src = '/assets/images/bp-homepage-artist-' + ctaIdx + '.png';
					ctaImg.classList.remove('blur-out');

					preloadNextImg();

				}, 500);
			}

			const preloadNextImg = function()
			{
				if(preloadCounter < ctaMax)
				{
					preloadCounter++;

					var preload = new Image();
					preload.src = '/assets/images/bp-homepage-artist-' + (ctaIdx == ctaMax ? ctaMin : (ctaIdx + 1)) + '.png';
				}
			}

			const ctaObserver = new window.IntersectionObserver((entries) => {

				if (entries[0].isIntersecting)
				{
					ctaInterval = setInterval(ctaImgRotator, 8000);
				}
				else
				{
					clearInterval(ctaInterval);
				}
			});

			ctaObserver.observe(ctaText);

			preloadNextImg();
		}
	}

	createBlogPost()
	{
		new this.BlogPost();
	}

	createBandCarousels()
	{
		document.querySelectorAll('.band-carousel').forEach(car => {

			new this.BandCarousel(car);
		});
	}

	openRankComparisonModal(rankerKey)
	{
		bp.openModal('.rank-comparison-modal');

		document.querySelectorAll('.ranker-modal ul li').forEach(function(li)
		{
			li.classList.remove('pulsate');
			if(li.dataset.key == rankerKey)
			{
				setTimeout(function()
				{
					this.classList.add('pulsate');
				}.bind(li), 200);
			}
		});
	}

	// openRankerModal(eid, rankerKey)
	// {
	// 	bp.openModal('.ranker-modal', function()
	// 	{
	// 		this.saveRankerOrder(eid);
	// 	}.bind(this));

	// 	document.querySelectorAll('.ranker-modal ul li').forEach(function(li)
	// 	{
	// 		li.classList.remove('pulsate');
	// 		if(li.dataset.key == rankerKey)
	// 		{
	// 			setTimeout(function()
	// 			{
	// 				this.classList.add('pulsate');
	// 			}.bind(li), 200);
	// 		}
	// 	});

	// 	const scrollContainer = document.querySelector('.ranker-modal .scroll-container');
	// 	const aside = document.querySelector('.ranker-modal aside');

	// 	if(aside && scrollContainer.scrollHeight <= scrollContainer.clientHeight)
	// 	{
	// 		aside.remove();
	// 	}
	// }

	// saveRankerOrder(eid)
	// {
 	// 	var saveUrl = '/rockstar/save-ranking-order';
	// 	var data = [];
	// 	document.querySelectorAll('.ranker-modal ul li').forEach(function(li)
	// 	{
	// 		data.push(li.dataset.key);
	// 	});

	// 	let jsonData = JSON.stringify(data);
	// 	let formData = new FormData();
	// 	let csrfTokenName = document.querySelector('meta[name="csrf-token-name"]').getAttribute('content');
	// 	let csrfTokenValue = document.querySelector('meta[name="csrf-token-value"]').getAttribute('content');

	// 	formData.append('eid', eid);
    //     formData.append('data', jsonData);
	// 	formData.append(csrfTokenName, csrfTokenValue);

	// 	fetch(saveUrl, { method: 'POST', body: formData })
	// 		.then((response) => {
	// 		    if (response.ok)
	// 		    {
	// 		    	return response.text();
	// 		    }
	// 		}).then((response) => {
	// 		}).catch((error) => {
	// 		    console.error("Error:", error);
	// 		});
	// }

	BandCarousel = class
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

			this.slides.forEach(slide => {
				let min = -4, max = 4;
				let deg = Math.random() * (max - min) + min;
				slide.style.transform = 'rotate(' + deg + 'deg)';

			});

			this.addEventListeners();

			this.createAnimationFrame();
		}

		addEventListeners()
		{
			this.resize = function()
			{
				this.slides = this.slider.querySelectorAll('.slide');
				let sliderWid = 0;

				this.slides.forEach(slide => {
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

	BlogPost = class
	{
		constructor()
		{
			this.readingProgress();
			this.toc();
			this.instagram();
			this.youtubeLoader();
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

							if(toc && !commentsVisible && !relatedVisible)
							{
								toc.classList.remove('fadeaway');
							}
						}
					}
				});
			}, { threshold: 0, rootMargin: `-70px 0px 0px 0px` });

			if(blogHeader && blogContainer)
			{
				observer.observe(blogHeader);
			}
			observer.observe(blogComments);
			observer.observe(blogRelated);

			if(toc)
			{
				toc.querySelector('.section-title').addEventListener('click', function(e)
				{
					toc.querySelector('ul').classList.toggle('close');
				});
			}
		}

		youtubeLoader()
		{
			const yts = document.querySelectorAll('.yt-loader');

			const observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting)
					{
						observer.unobserve(entry.target);

						let title = entry.target.dataset.caption.length > 0 ? entry.target.dataset.caption : entry.target.dataset.title;
						let url = `https://www.youtube.com/embed/${entry.target.dataset.id}`;
						if(entry.target.dataset.minute.length)
						{
							url += `?start=${entry.target.dataset.minute}`;
						}

						let html = `<figure class="video">
										<div class="player">
											<iframe width="560" height="315" src="${url}" title="${title}" frameborder="0" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
										</div>`;

						if(entry.target.dataset.caption.length > 0)
						{
							html += `<figcaption>${entry.target.dataset.caption}</figcaption>`;
						}

						html += "</figure>";

						entry.target.outerHTML = html;
					}
				});
			}, { threshold: 0, rootMargin: `150px` });

			yts.forEach((yt) => {
				observer.observe(yt);
			});
		}

		readingProgress()
		{
			const rtime = document.getElementById('reading-time');

			if(rtime)
			{
				const progress = rtime.querySelector('.reading-progress');
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

				BandPioneerUX.addStickyListener(function(isSticky)
				{
					if(isSticky)
					{
						rtime.classList.add("show");
					}
					else
					{
						rtime.classList.remove("show");	
					}
				});

		    	window.addEventListener("scroll", () => {
		      		setReadingProgress();
		    	});

		    	setReadingProgress();
		    }
		}

		instagram()
		{
			// Instagram Videos
			// ... this uses window scroll because IntersectionObserver was scoring 20 pts less in lighthouse

			var instas = document.querySelectorAll(".instagram-media");
			var embeddedCount = 0;

			if(instas.length > 0)
			{
				const instaScroll = function(insta)
				{
					instas.forEach(insta => {
						if(insta.embedded === undefined || !insta.embedded)
						{
							const scrollPosition = window.scrollY + window.innerHeight;

							if (insta.getBoundingClientRect().top + window.scrollY < scrollPosition)
							{
								insta.embedded = true;
								embeddedCount++;

								const firstChild = insta.firstChild;
								const iframe = document.createElement('iframe');

								iframe.src = `https://www.instagram.com/p/${insta.dataset.id}/embed/?cr=1&v=14&wp=540&rd=https%3A%2F%2Fbandpioneer.com`;
								iframe.setAttribute("style", "background: white; max-width: 540px; width: calc(100% - 2px); border-radius: 3px; border: 1px solid rgb(219, 219, 219); box-shadow: none; display: block; margin: 0px 0px 12px; min-width: 326px; padding: 0px;");
								iframe.setAttribute("allowtransparency", "true");
								iframe.setAttribute("allowfullscreen", "true");
								iframe.setAttribute("frameborder", "0");
								iframe.setAttribute("height", "705");
								iframe.setAttribute("scrolling", "no");

								insta.insertBefore(iframe, firstChild);

								if(embeddedCount >= instas.length)
								{
									window.removeEventListener('scroll', instaScroll); 
								}
							}
						}
					});
				}

				window.addEventListener('scroll', instaScroll); 
			}

			
		}
	}
}
