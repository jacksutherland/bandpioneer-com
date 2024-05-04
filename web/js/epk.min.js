class EPK {

    static IsBreakpoint(breakpoint)
    {
        // EPK.IsBreakpoint('sm') returns true only if width is 576 or less.
        // Width 900px returns true for sm & md, but not lg or xl.

        switch(breakpoint)
        {
            case "sm":
                return window.matchMedia('(max-width: 576px)').matches;
            case "md":
                return window.matchMedia('(max-width: 768px)').matches;
            case "lg":
                return window.matchMedia('(max-width: 992px)').matches;
            case "xl":
                return window.matchMedia('(max-width: 1200px)').matches;
            default:
                return window.matchMedia('(max-width: 576px)').matches;
        }
    }

    static Modal = {
        overlay: document.querySelector('#modal.overlay'),
        modal: document.querySelector('#modal .modal'),
        content: document.querySelector('#modal .modal .content'),
        closeBtn: document.querySelector('#modal .modal .close'),
        init: function()
        {
            EPK.Modal.closeBtn.addEventListener('click', function(e)
            {
                e.preventDefault();

                EPK.Modal.close();
            });
            EPK.Modal.overlay.addEventListener('click', function()
            {
                EPK.Modal.close();
            });
            EPK.Modal.modal.addEventListener('click', function(e)
            {
                e.stopPropagation();
            });
        },
        load: function(content)
        {
            EPK.Modal.content.innerHTML = content.outerHTML;
        },
        open: function()
        {
            EPK.Modal.overlay.classList.add('open');
            EPK.Modal.modal.classList.add('open');
        },
        close: function()
        {
             EPK.Modal.overlay.classList.remove('open');
        }
    };

    static ImageModals()
    {
        EPK.Modal.init();

        const images = document.querySelectorAll('[data-modal]');

        images.forEach((image) => 
        {
            image.addEventListener('click', () => 
            {
                if(EPK.IsBreakpoint('md')) return false;

                EPK.Modal.load(image);
                EPK.Modal.open();
            });
        });
    }
}

