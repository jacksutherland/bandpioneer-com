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
}