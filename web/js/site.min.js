let BandPioneer = {
	Site: class {
		constructor(tempVar)
		{
			this.tempVar = tempVar;

			this.search = {
				ele: document.querySelector('#search'),
				icon: document.querySelector('#search svg'),
				input: document.querySelector('#search input')
			};

			this.addHeaderEvents();
			this.addMenuEvents();
			this.addSearchEvents();

			this.readingProgress();
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
			let open = document.querySelector('#open-menu');
			let close = document.querySelector('#close-menu');


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
		}

		addSearchEvents()
		{
			// this is needed to avoid a refresh bug, but not sure why?!?!??
			document.querySelector('input[name="q"]').addEventListener('click', function(e)
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
	}
}