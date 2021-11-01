/**
* PHP Email Form Validation - v3.1
* URL: https://bootstrapmade.com/php-email-form/
* Author: BootstrapMade.com
*/
(function () {
  "use strict";

  let forms = document.querySelectorAll('.php-email-form');

  forms.forEach( function(e) {
    e.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const thisForm = this;
      const formData = new FormData( thisForm );
      const action = thisForm.getAttribute('action');
      

      if ( !action ) {
        displayError( thisForm, 'Form action has not been set' );
        return;
      }

      // Hide previous errors
      thisForm.querySelector('.loading').classList.add('d-block');
      thisForm.querySelector('.error-message').classList.remove('d-block');
      thisForm.querySelector('.sent-message').classList.remove('d-block');

      fetch(action, {
        method: 'POST',
        body: formData,
      })
      .then( function(response) {
        if (!response.ok) throw response.statusText;
        return response.json();
      })
      .then( function(data) {
        if (data.code !== 200) throw data.message;
        // Stop laoder and display success message
        thisForm.querySelector('.loading').classList.remove('d-block');
        thisForm.querySelector('.sent-message').classList.add('d-block');

        // Clear the email
        thisForm.reset();
      })
      .catch( function(err) {
        displayError(thisForm, err)
      })

    })
  })

  function displayError(thisForm, error) {
    thisForm.querySelector('.loading').classList.remove('d-block');
    thisForm.querySelector('.error-message').innerHTML = error;
    thisForm.querySelector('.error-message').classList.add('d-block');
  }

})();
