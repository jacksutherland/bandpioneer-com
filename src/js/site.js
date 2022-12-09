let BandPioneer = {

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
			let categories = document.querySelectorAll('.categories nav ul li a');

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

			BandPioneer.each(categories, function()
			{
				this.addEventListener('mouseover', function(e)
				{
					e.preventDefault();

					BandPioneer.hoveringOverCategory = true;
					BandPioneer.categoryId = this.dataset.id;

					let categoryDescription = document.querySelector('#category-description');
					categoryDescription.querySelector('.container').innerText = this.dataset.description;

					BandPioneer.categoryTimeout = setTimeout(function()
					{
						if(BandPioneer.hoveringOverCategory && this.catId == BandPioneer.categoryId)
						{
							this.catDesc.classList.add('show');
						}
					}.bind({ catDesc: categoryDescription, catId: this.dataset.id }), 1000);
				});

				this.addEventListener('mouseleave', function(e)
				{
					e.preventDefault();

					BandPioneer.categoryId = 0;
					BandPioneer.hoveringOverCategory = false;
					clearTimeout(BandPioneer.categoryTimeout);

					setTimeout(function()
					{
						if(!BandPioneer.hoveringOverCategory)
						{
							document.querySelector('#category-description').classList.remove('show');
						}
					}, 500);
				});
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