
// #region < GLOBAL VARIABLES >

// #endregion

// #region  < FUNCTIONS >


	function serverNotification(message) {

		$('#toastContainerServer div.toast-body').text(message);

		const toast = new bootstrap.Toast($('#addedPersonnelNotification'));

		toast.show();

	}

	function errorNotification(error) {

		$('#toastContainerError div.toast-body').text(error);

		const toast = new bootstrap.Toast($('#unkownErrorNotification'));

		toast.show();

	}

	function toggleSearchDropdown() {
		$("#searchedDataContainer").toggleClass('d-none');

		$("#SearchBackground").toggleClass('userSearching');
		$("#SearchBackground").toggleClass('d-none');

		$('body').toggleClass('stop-scroll');
	}

	function resetForm(form) {
		$(form).trigger("reset");
		$(form).validate().resetForm();
	}

// #endregion

// #region < HTML >

	function personelCard(firstName, lastName, department, location, id) {
		return `
			<div class="card bg-purple" data-personnelID="${id}">
				<div class="card-body d-flex justify-content-between">
					<div class="card-body-main d-flex flex-column flex-md-row align-items-md-center">
						<h5 id="name" class="mb-md-0 me-3">${firstName} ${lastName}</h6>
						<div>
							<small class="badge badge-department rounded-pill">${department}</small>
							<small class="badge badge-location rounded-pill">${location}</small>
						</div>
					</div>
					<div class="mb-md-0 edit-and-delete-container d-flex flex-row align-items-center">
						<button 
							class="delete-card-btn btn btn-link danger me-2"
							data-bs-toggle="modal"
							data-bs-target="#modalDeletePersonnel"
						>
							<i class="far fa-trash-alt"></i>
						</button>
						<button 
							class="edit-card-btn btn btn-link"
							data-bs-toggle="modal"
							data-bs-target="#modalEditPersonnel"
						>
							<i class="fas fa-info-circle"></i>
						</button>
					</div>
				</div>
			</div>
	    `;
	}

	function departmentCard(name, location, id) {
		return `
			<div class="card personnel-cards bg-purple" data-departmentID="${id}" data-name="${name}">
				<div class="card-body d-flex justify-content-between">
					<div class="name-and-department-container d-flex flex-column flex-md-row align-items-md-center">
						<h5 class="mb-md-0 me-3">${name}</h6>
						<div>
							<small class="badge badge-location rounded-pill">${location}</small>
						</div>
					</div>
					<div class="mb-md-0 edit-and-delete-container d-flex flex-row align-items-center">
						<button 
							class="delete-card-btn btn btn-link danger me-2"
							data-bs-toggle="modal"
							data-bs-target="#modalDeleteDepartment"
						>
							<i class="far fa-trash-alt"></i>
						</button>
						<button 
							class="edit-card-btn btn btn-link"
							data-bs-toggle="modal"
							data-bs-target="#modalEditDepartment"
						>
							<i class="fas fa-info-circle"></i>
						</button>
					</div>
				</div>
			</div>
		`
	}

	function locationCard(name, numOfPersonnel, id) {
		return `
			<div class="card personnel-cards bg-purple" data-locationID="${id}" data-name="${name}">
				<div class="card-body d-flex justify-content-between">
					<div class="name-and-department-container d-flex flex-column flex-md-row align-items-md-center">
						<h5 class="mb-md-0 me-3">${name}</h6>
						<div>
							<small class="badge badge-personnel rounded-pill">${numOfPersonnel} personnel</small>
						</div>
					</div>
					<div class="mb-md-0 edit-and-delete-container d-flex flex-row align-items-center">
						<button 
							class="delete-card-btn btn btn-link danger me-2"
							data-bs-toggle="modal"
							data-bs-target="#modalDeleteLocation"
						>
							<i class="far fa-trash-alt"></i>
						</button>
						<button 
							class="edit-card-btn btn btn-link"
							data-bs-toggle="modal"
							data-bs-target="#modalEditLocation"
						>
							<i class="fas fa-info-circle"></i>
						</button>
					</div>
				</div>
			</div>
		`
	}

	function departmentOptions(id, name) {
		return `
			<option value="${id}">${name}</option>
		`;
	}

// #endregion

// #region < ON CLICK >

	// #region < HIDE FORM MODAL >

		$('.modal').on('hide.bs.modal', function() {
			const form = $(this).find('form');
			
			// Reset all form data when leaving modal
			resetForm(form);

			// Forms have dependecies
			if ( form.hasClass('form-has-dependecies') ) {
				// Remove dependecy information from delete forms
				$('.has-dependencies-text', form).addClass('d-none');
				$('.form-check-dependecy', form).addClass('d-none');
			}

			// Edit forms
			if ( form.hasClass('edit-form') ) {
				// Re-lock the lock
				$('.lock').removeClass('unlocked');

				// Revert input to readonly / disabled
				$('input', form).attr('readonly', true);
				$('select', form).attr('disabled', true);
				$('button', form).attr('disabled', true);
			}
		})

	// #endregion 

	// #region < FORM UNLOCK BUTTON >

		$(".modal").click( function({target}) {

			const form = $('form', this);
			const lockClicked = $(target).hasClass('lock');

			if (!lockClicked) return;

			// Re-lock and lock
			$(target).toggleClass('unlocked');
			const isReadonly = $(target).hasClass('unlocked') ? false : true;

			// disable or enable input depending on if the lock is unlocked / locked
			$('input', form).attr('readonly', isReadonly);
			$('select', form).attr('disabled', isReadonly);
			$('button', form).attr('disabled', isReadonly);
		});

	// #endregion

	// #region < DISPLAY DATA IN FORMS >

		// #region < PERSONNEL >

			$('#companyPersonnelContainer').click(({target}) => {
		
				if (target.nodeName !== 'BUTTON') return;
		
				// Get personnelID from HTML .card
				const personnelID = $(target).parents('.card').data('personnelid');
		
				const editForm = $('#editPersonnel');
				const deleteForm = $('#deletePersonnel');
			
				const editButtonSelected = target.classList.contains('edit-card-btn');
				const deletedButtonSelected = target.classList.contains('delete-card-btn');

				$.get('./libs/php/routes/getPersonnelByID.php', { id: personnelID })
					.done( function({data}) {
						handleSuccess(data);
					})
					.fail( function(err) {
						console.error(err);
					})		
					
				function handleSuccess(response) {
					const selectedPersonnel = response[0];
					
					if ( editButtonSelected ) {
					
						// Display personnel details in input fields
						$('input[name="firstName"]', editForm).val(selectedPersonnel.firstName);
						$('input[name="lastName"]', editForm).val(selectedPersonnel.lastName);
						$('input[name="email"]', editForm).val(selectedPersonnel.email);
						$(`select[name="departmentID"] option[value='${selectedPersonnel.departmentID}']`, editForm).attr('selected', true);
						$('input[name="jobTitle"]', editForm).val(selectedPersonnel.jobTitle);
						$('input[name="personnelID"]', editForm).val(selectedPersonnel.id);
			
						// Display the form
						$('#editPersonnelFormContainer').toggleClass('d-none');
			
					}
			
					if ( deletedButtonSelected ) {
			
						// Insert user name into form
						$('span', deleteForm).text(`${selectedPersonnel.firstName} ${selectedPersonnel.lastName}`);

						// Display personnel details in input fields
						$('input[name="id"]', deleteForm).val(selectedPersonnel.id);
			
					}

				}
			})

		// #endregion

		// #region < DEPARTMENT > 

			$('#companyDepartmentsContainer').click( function ({target}) {
				if (target.nodeName !== 'BUTTON') return;
		
				// Get departmentID from HTML .card
				const departmentID = $(target).parents('.card').data('departmentid');

				const editForm = $('#editDepartment');
				const deleteForm = $('#deleteDepartment')

				const editButtonSelected = target.classList.contains('edit-card-btn');
				const deleteButtonSelected = target.classList.contains('delete-card-btn');

				$.get('./libs/php/routes/getDepartmentByID.php', { id: departmentID })
					.done( function({data}) {
						handleSuccess(data, data.hasDependecies);
					})
					.fail( function(err) {
						console.error(err);
					})

				function handleSuccess(response, hasDependecies) {
					const selectedDepartment = response[0];

					if ( editButtonSelected ) {

						// Display department details in input fields
						$('input[name="name"]', editForm).val(selectedDepartment.name);
						$(`select[name="locationID"] option[value="${selectedDepartment.locationID}"]`, editForm).attr('selected', true);
						$('input[name="departmentID"]', editForm).val(selectedDepartment.id);
					}

					if ( deleteButtonSelected ) {

						if ( hasDependecies ) {
							// Prevent user deleting department
							$(".has-dependencies-text", deleteForm).removeClass('d-none');
							$('.check-with-user', deleteForm).hide();
							$('button', deleteForm).hide();
						} else {
							// Display department details in input fields
							$('input[name="id"]', deleteForm).val(selectedDepartment.id);
							$('span', deleteForm).text(selectedDepartment.name);
							$('.check-with-user', deleteForm).show();
							$('button', deleteForm).show();
						}

					}
				}

			})

		// #endregion

		// #region < LOCATION >

			$('#companyLocationsContainer').click( function({target}) {
				if (target.nodeName !== 'BUTTON') return;

				const locationID = $(target).parents('.card').data('locationid');

				const editForm = $('#editLocation');
				const deleteForm = $('#deleteLocation');

				const editButtonSelected = $(target).hasClass('edit-card-btn');
				const deleteButtonSelected = $(target).hasClass('delete-card-btn');
				
				$.get('./libs/php/routes/getLocationByID.php', { id: locationID })
					.done( function({data}) {
						handleSuccess(data, data.hasDependecies);
					})
					.fail( function(err) {
						console.error(err);
					})

				function handleSuccess(response, hasDependecies) {
					const selectedLocation = response[0];

					if ( editButtonSelected ) {

						$('input[name="name"]', editForm).val(selectedLocation.name);
						$('input[name="locationID"]', editForm).val(selectedLocation.id);

					}

					if ( deleteButtonSelected ) {

						if ( hasDependecies ) {
							// Prevent user deleting department
							$(".has-dependencies-text", deleteForm).removeClass('d-none');
							$('.check-with-user', deleteForm).hide();
							$('button', deleteForm).hide();
						} else {
							// Display department details in input fields
							$('input[name="id"]', deleteForm).val(selectedLocation.id);
							$('span', deleteForm).text(selectedLocation.name);
							$('.check-with-user', deleteForm).show();
							$('button', deleteForm).show();
						}

					}

				}

			})

		// #endregion

	// #endregion

	// #region < SEARCH >

		$('#searchPersonnelForm').focusin( function() {
			toggleSearchDropdown();
		})
		
		$('#searchPersonnelForm').focusout( function({relatedTarget}) {

			
			const clickedAnchorTag = relatedTarget;

			if ( clickedAnchorTag?.nodeName === 'A' ) {

				const headerHeight = $('header').outerHeight() + 8;

				const id = $(clickedAnchorTag).data('id');
				const table = $(clickedAnchorTag).data('table');

				$('html, body')
					.animate({
						scrollTop: $(`div[data-${table}ID='${id}']`).first().offset().top - headerHeight
					});
				
				$(`div[data-${table}ID='${id}']`)
					.attr("tabindex" , -1)
					.focus()
					.focusout( function() {
						$(this).removeAttr("tabindex");
					})

			} 

			toggleSearchDropdown();

		})

		$('#searchPersonnel').keyup( function() {
			if( $('#searchPersonnel').val() === "" ) {
				$('#searchedDataContainer .text-warning').addClass('d-none');
				$('#searchedDataContainer .list-group').empty();
			}
		});

	// #endregion

	// #region < REFRESH >

		$('#refreshPageBtn').click( function() {
			location.reload();
		})

	// #endregion

	// #region < TABS >
	
		// Remove data from search input when user tabs to another location
		$('header .nav-item .btn-link').click( function() {
			$('#searchPersonnel').val("");
			$('#searchedDataContainer .text-warning').addClass('d-none');
			$('#searchedDataContainer .list-group').empty();
		})

	// #endregion

// #endregion

// #region < ON LOAD >
	loadData();

	function loadData() {
		// Clear any data first
		$('.empty-data').empty();

		// Add option at top of select in forms
		$('.department-select-data').append(
			'<option selected value="">Select Department</option>'
		)
		$('.location-select-data').append(
			'<option selected value="">Select Location</option>'
		)

		// Display preloader until all data has loaded, only on window load
		$.when(loadPersonnel(), loadDepartment(), loadLocation())
			.done( function() {
				if ( $('#preloader').hasClass('d-none') ) return;
				$('#preloader').addClass('d-none');
			})

	}

	function loadPersonnel() {
		return  $.get(`./libs/php/routes/getAll.php`)
					.done((response) => {

						const allPersonnel = response.data;

						// Display all people to DOM
						allPersonnel.forEach((person) => {
							$('#companyPersonnelContainer').append(

								personelCard( person.firstName, person.lastName, person.department, person.location, person.id)
									
							)
						})

					})
					.fail((err) => {

						console.error(err);
						errorNotification("Cannot load personnel");

					})
	}

	function loadDepartment() {
		return 	$.get(`./libs/php/routes/getAllDepartments.php`)
					.done((response) => {

						const allDepartments = response.data;
						
						allDepartments.forEach((department) => {
							$('#companyDepartmentsContainer').append(

								departmentCard(department.name, department.locationName, department.id)

							)

							// Select department when creating new employee
							$('.department-select-data').append(

								departmentOptions(department.id, department.name)

							)
						})
					})
					.fail((err) => {

						console.error(err);
						errorNotification("Cannot load departments")

					})
	}

	function loadLocation() {
		return 	$.get(`./libs/php/routes/getLocations.php`)
					.done((response) => {

						const locations = response.data;

						locations.forEach((location) => {
							$('#companyLocationsContainer').append(

								locationCard(location.name, location.numOfPersonnel, location.id)

							)

							$('.location-select-data').append(
								`
									<option value="${location.id}" >${location.name}</option>
								`
							)
						})

					})
					.fail((err) => {

						console.error(err);
						errorNotification("Cannot load locations");

					})
	}

// #endregion

// #region < ON SUBMIT >

	// #region < VALIDATION >

		/**
		 * The form being validated is sent to this variable as a jquery object,
		 * allowing all functions to access the current form validation. Specifically
		 * when handling validation errors sent from the server
		 */
		let currentFormBeingValidated;

		// Edit personnel
		$('#editPersonnel').validate({
			submitHandler: function(form) {
				loading(true, form)
				// Set current form for error handling
				currentFormBeingValidated = $('#editPersonnel');

				const firstName = $(`input[name="firstName"]`, $(form)).val();
				const lastName = $(`input[name="lastName"]`, $(form)).val();

				$.post('./libs/php/routes/updatePersonnelByID.php', $(form).serialize())
					.done( function() {

						loadData();
						$('.modal').modal('hide');
						serverNotification(`${firstName} ${lastName} edited`);

					})
					.fail( handleValidationError )
					.always( function() { loading(false, form) } );

			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		})

		// Add personnel
		$('#addPersonnel').validate({
			submitHandler: function(form) {
				loading(true, form);
				// Set current form for error handling
				currentFormBeingValidated = $('#addPersonnel');

				const firstName = $(`input[name="firstName"]`, $(form)).val();
				const lastName = $(`input[name="lastName"]`, $(form)).val();

				$.post('./libs/php/routes/insertPersonnel.php', $(form).serialize())
					.done( function() {

						loadData();
						$('.modal').modal('hide');
						serverNotification(`${firstName} ${lastName} added`);

					})
					.fail( handleValidationError )
					.always( function() { loading(false, form) } );

			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		});

		// Edit department
		$('#editDepartment').validate({
			submitHandler: function(form) {
				loading(true, form)

				currentFormBeingValidated = $('#editDepartment');

				const departmentName = $('input[name="name"]', $(form)).val();
		
				$.post('./libs/php/routes/updateDepartment.php', $(form).serialize())
					.done( function() {

						loadData();
						$('.modal').modal('hide');
						serverNotification(`${departmentName} edited`);

					})
					.fail( handleValidationError )
					.always( function() { loading(false, form) } );
	
			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		})

		// Add department 
		$('#addDepartment').validate({
			submitHandler: function( form ) {
				loading(true, form);

				currentFormBeingValidated = $('#addDepartment');

				const departmentName = $('input[name="name"]', $(form)).val();

				$.post('./libs/php/routes/insertDepartment.php', $(form).serialize())
					.done( function() {

						loadData();
						$('.modal').modal('hide');
						serverNotification(`${departmentName} added`);

					})
					.fail( handleValidationError )
					.always( function() { loading(false, form) } );
	
			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		})

		// Delete department
		$('#deleteDepartment').validate({
			submitHandler: function(form) {

				currentFormBeingValidated = $('#deleteDepartment');

				const departmentName = $('span', $(form)).first().text();

				$.post('./libs/php/routes/deleteDepartmentByID.php', $(form).serialize())
					.done( function() {
	
						loadData();
						$('.modal').modal('hide');
						serverNotification(`${departmentName} deleted`);
						
					})
					.fail( function(err) {
	
						// If the error is not related to dependecies
						if (err.status !== 400) handleDatabaseError(err);
	
						// Display dependency information to user
						$('.has-dependencies-text', form).removeClass('d-none');
						$('.form-check-dependecy', form).removeClass('d-none');
	
					}).always ( function() {
						$( 'input[type="checkbox"]', form).prop('checked', false)
					})
			},
			messages: {
				deleteWithDependecy: {
					required: "You must agree if you want to delete the department",
				}
			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		})

		// Add location
		$('#addLocation').validate({
			submitHandler: function(form) {
				loading(true, form);

				currentFormBeingValidated = $('#addLocation');

				const locationName = $('input[name="name"]', $(form)).val();

				$.post('./libs/php/routes/insertLocation.php', $(form).serialize())
					.done( function() {

						loadData();
						$('.modal').modal('hide');
						serverNotification(`${locationName} added`);
						
					})
					.fail( handleValidationError )
					.always( function() { loading(false, form) } );
			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		})

		// Edit location
		$('#editLocation').validate({
			submitHandler: function(form) {
				loading(true, form);

				currentFormBeingValidated = $('#editLocation');

				const locationName = $('input[name="name"]', $(form)).val();

				$.post('./libs/php/routes/updateLocation.php', $(form).serialize())
					.done( function() {

						loadData();
						$('.modal').modal('hide');
						serverNotification(`${locationName} edited`);


					})
					.fail( handleValidationError )
					.always( function() { loading(false, form) } );
			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		})

		// Delete location
		$('#deleteLocation').validate({
			submitHandler: function(form) {

				currentFormBeingValidated = $('#deleteLocation');

				const locationName = $('span', $(form)).first().text();

				$.post('./libs/php/routes/deleteLocationByID.php', $(form).serialize())
					.done( function() {
	
						loadData();
						$('.modal').modal('hide');
						serverNotification(`${locationName} deleted`);
						
					})
					.fail( function(err) {
	
						// If the error is not related to dependecies
						if (err.status !== 400) handleDatabaseError(err);
	
						// Display dependency information to user
						$('.has-dependencies-text', form).removeClass('d-none');
						$('.form-check-dependecy', form).removeClass('d-none');
	
					})
					.always ( function() {
						$( 'input[type="checkbox"]', form).prop('checked', false)
					})
			},
			messages: {
				deleteWithDependecy: {
					required: "You must agree if you want to delete the location",
				}
			},
			validClass: "is-valid",
			errorClass: "is-invalid"
		})
		

	// #endregion

	// #region < ONLY SUBMIT >

		// Delete personnel
		$('#deletePersonnel').submit( function( event ) {

			event.preventDefault();
			const form = event.target;
			loading(true, form);

			const personnelName = $('span', $(form)).first().text();

			$.post('./libs/php/routes/deletePersonnelByID.php', $(form).serialize())
				.done( function() {

					loadData();
					$('.modal').modal('hide');
					serverNotification(`${personnelName} deleted`);

				})
				.fail( function() {
					errorNotification(`Unable to delete personnel from the server`);
				})
				.always( function() { loading(false, form) } );
		});

		// Search database
		$('#searchPersonnelForm').submit ( function( event ) {

			event.preventDefault();

			const form = event.target;

			$.post('./libs/php/routes/search.php', $(form).serialize())
				.done( function({data}) {

					// Empty pervious search data
					$('#searchedDataContainer .list-group').empty();

					// select data depending on which tab the user is on. For example,
					// if the user is in 'personnel' select only personnel data from data object
					const tableToShow = $('header .active').text().trim().toLowerCase();

					data[tableToShow].forEach( function(row) {
						$('#searchedDataContainer .list-group').append(
							`
								<a
								href="#"
								class="list-group-item bg-purple text-white list-group-item-action"
								data-id="${row.id}"
								data-table="${tableToShow}"
								aria-current="true"
							>
									<div class="d-flex align-items-center">
										<h6 class="mb-0 me-3">${row.name}</h6>
										<small class="badge badge-${tableToShow} rounded-pill">${tableToShow}</small>
									</div>
								</a>
							`
						)

					})

					// If no divs were created then data is empty

					if ($('#searchedDataContainer .list-group').children().length === 0) {
						$('#searchedDataContainer .text-warning').removeClass('d-none');
					} else {
						$('#searchedDataContainer .text-warning').addClass('d-none');
					}

				})
				.fail( function(err) {
					console.log(err);
				})

		})

	// #endregion

	// #region < SUBMIT FUNCTIONS >

		function handleValidationError( error ) {

			if (error.status !== 400) handleDatabaseError(error);

			// Display errors directly from server-side object
			try {

				const validation = currentFormBeingValidated.validate();

				validation.showErrors(error.responseJSON.data);

			} catch (error) {
				errorNotification("Sorry, we are unable to carry out this request.");
				console.error(error);
			}

		}

		function handleDatabaseError(error) {
			errorNotification("Something went wrong with the server");
			console.error(error);
		}

		function loading(isLoading, form) {
			const button = $('button', form);
			const spinner = $('button .spinner-border', form);

			if (isLoading) {
				button.addClass('button-loading');
				spinner.removeClass('d-none');
			} else {
				button.removeClass('button-loading');
				spinner.addClass('d-none');
			}

		}

	// #endregion

// #endregion