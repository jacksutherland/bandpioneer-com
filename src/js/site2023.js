/**
 * Band Pioneer, LLC - October 2023
 * 
 * The primary JS file for the public website
 */

class BandPioneer
{
	constructor()
	{
		this.addHeaderEvents();
		this.addTopicMenuEvents();
	}

	addHeaderEvents()
	{
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

				if (!entries[0].isIntersecting)
				{
					body.classList.add('sticky');
				}
				else
				{
					body.classList.remove('sticky');
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

	createBandCarousels()
	{
		console.log("createBandCarousels");

		document.querySelectorAll('.band-carousel').forEach(car => {

			new this.BandCarousel(car);

		});
	}

	BandCarousel = class
	{
		constructor(carousel)
		{
			console.log("BandCarousel constructor");

			this.scrollSpeed = 0.3;

			this.direction = 'left';

			this.slider = carousel.querySelector('.band-slider');

			this.slides = this.slider.querySelectorAll('.slide');

			this.carouselAnimationRequest = null;

			this.carouselAnimationFrame = null;

			// Randomly rotate images

			// BandPioneer.each(this.slides, function(idx, slide)
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

				// BandPioneer.each(this.slides, function(idx, slide)
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
}









