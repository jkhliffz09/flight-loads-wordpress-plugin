(function($){
  const pfl_ajax = window.pfl_ajax

  const AIRPORTS_VERSION = "v6";
  const AIRLINES_VERSION = "v6";

  let allAirlines = [];
  let allAirports = [];
  let allAirlinesPublic = [];
  var isUserEmailVerified = false;

  $(document).ready(() => {

    preloadData();
    // Application Form Handler
    if ($("#pfl-application-form").length) {
      initApplicationForm()
    }

    // Flight Request Form Handler
    if ($("#pfl-flight-request-form").length) {
      initFlightRequestForm()
    }

    // Password Update Form Handler
    if ($("#pfl-password-form").length) {
      initPasswordForm()
    }

    // Preload airline list
    $.get(pfl_ajax.ajax_url, { action: "pfl_get_all_airlines" }, (response) => {
      if (response.success) {
        allAirlines = response.data;
        $("#airline_search")
            .prop("disabled", false)
            .attr("placeholder", "Type to search...");
      }
    });

    $('.comment-toggle').on('click', function() {
      let reqId = $(this).data('request');
      $('#comments-' + reqId).toggle();

      let flightLoad = $('#flightload-' + reqId);
      if(flightLoad.is(':visible')){
        flightLoad.hide();
      }

    });

    $('.flightload-show, .cancel-btn').on('click', function(){
      let reqId = $(this).data('request');

      $('#flightload-' + reqId).toggle();

      let comments = $('#comments-' + reqId);
      if (comments.is(':visible')) {
        comments.hide();
      }
    })

    initAirlineAutocomplete("#airline_search", "#airline_id", "#airline_suggestions");// Send Verification Code for user email
    
    //Verify Personal Email
    $("#verifyEmail").on("click", function () {
      $("#user_verification_section").remove();
      var email = $("#email").val()


      clearFieldError("email")

      if (!email) {
        showFieldError("email", "Please enter your email address")
        return
      }

      $(this).prop("disabled", true).text("Sending...")

      $.post(
        pfl_ajax.ajax_url,
        {
          action: "pfl_send_verification",
          email: email,
          airline_id: '999999', 
          nonce: pfl_ajax.nonce,
        },
        (response) => {
          //console.log(response);
          if (response.success) {
            // show input for code
            if (!$("#user_verification_section").length) {
              $("#verification_area").append(`
                <div id="user_verification_section" class="pfl-form-group" class="mt-3">
                  <label for="user_verification_code" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                  <div class="flex">
                    <input type="text" id="user_verification_code" name="user_verification_code" class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter code">
                    <button type="button" id="verifyUserCode" class="px-4 bg-green-600 text-white rounded-r-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 h-[34px]">Verify</button>
                  </div>
                </div>
              `)
            }
            showSuccessMessage("Verification code sent to your email")
          } else {
            showFieldError("email", response.data)
            $("#user_verification_section").remove();
          }
        },
      ).always(() => {
        $("#verifyEmail").prop("disabled", false).text("Send Code")
      })
    })

    function validateSearchField(){
      var isValid = true
      var requiredFields = ["from_airport_search", "to_airport_search", "travel_date_search"]

      clearAllErrors()

      requiredFields.forEach((field)=>{
        var input = $('[name="' + field + '"]')
        if(!input.val()){
          showFieldError(field, "This field is required");
          isValid = false;
        }
        
      })

      return isValid

    }

    $('#reset-btn').on('click', function(){
      $("#flight-results").empty();
      $("#scrollLeft").hide();
      $("#scrollRight").hide();
      jQuery("#search_fields").show();
      jQuery("#request-fields").hide();
      $("#pfl-flight-request-form")[0].reset();
    });

    let deleteRequestId = null; // store selected request id

    $('.delete-request').on('click', function(){
      
      deleteRequestId = $(this).data("request-id"); // get from link
      document.getElementById("deleteDialog").showModal();
    });

    // Confirm delete
    $("#confirmDelete").on("click", function () {
        if (!deleteRequestId) return;

        $(this).text('Deleting...');

        $.ajax({
            url: pfl_ajax.ajax_url, // WordPress AJAX endpoint
            type: "POST",
            data: {
                action: "pfl_delete_flight_request",
                request_id: deleteRequestId,
            },
            success: function (response) {
                if (response.success) {
                    alert("Deleted successfully!");
                    location.reload(); // reload or remove item from DOM
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function () {
                alert("AJAX request failed.");
            },
        });

        document.getElementById("deleteDialog").close();
    });

    /* SEARCH FLIGHT */
    $("#search_flights").on("click", function () {
      const from = $("#from_airport_search").val().trim();
      const to = $("#to_airport_search").val().trim();
      const date = $("#travel_date_search").val().replace(/-/g, ""); // YYYYMMDD
      $("#scrollLeft").hide();
      $("#scrollRight").hide();

      if (!validateSearchField()) {
        return;
      }

      // Disable button while loading
      $(this).prop("disabled", true).text("Searching...");

      $.post(
        pfl_ajax.ajax_url,
        {
          action: "pfl_search_flights",
          nonce: pfl_ajax.nonce,
          from,
          to,
          date
        },
        (response) => {
          if (response.success) {
            const flights = response.data;
            const resultsDiv = $("#flight-results");
            resultsDiv.empty();

            if (flights.length === 0) {
              $("#scrollLeft").hide();
              $("#scrollRight").hide();
              resultsDiv.append(`<p class="text-gray-500">No flights found.</p>`);
              return;
            }

            flights.forEach((f) => {
              $("#scrollLeft").show();
              $("#scrollRight").show();
              const dep = formatDateTime(f.departure_time);
              const arr = formatDateTime(f.arrival_time);
              const duration = formatDuration(f.duration);
              resultsDiv.append(`
                <div class="min-w-[200px] bg-white shadow-xl border p-4 rounded hover:shadow-md cursor-pointer"
                     onclick="selectFlight('${f.airline}', '${f.airline_code}' , '${f.number}', '${from}', '${to}', '${f.departure_time}', '${f.aircraft}')">
                  <p class="font-bold text-blue-600 text-sm">${f.airline_code}${f.number}/${f.aircraft}</p>
                  <p class="text-sm m-0 p-0 font-bold">${f.departure} → ${f.arrival}</p>
                  <p class="text-sm m-0 p-0">DEP: ${dep}</p>
                  <p class="text-sm m-0 p-0">ARR: ${arr}</p>
                  <p class="text-sm m-0 p-0">Duration: ${duration}</p>
                </div>
              `);
            });
          } else {
            resultsDiv.append(response.data ||  "Error fetching flights")
          }
        }
      ).always(() => {
        $("#search_flights").prop("disabled", false).text("Search Flights");
      });
    });

    // Verify Code
    $(document).on("click", "#verifyUserCode", function () {
      //console.log('Verified Clicked')
      var email = $("#email").val()
      var code = $("#user_verification_code").val()

      clearFieldError("user_verification_code")

      if (!code) {
        showFieldError("user_verification_code", "Please enter the verification code")
        return
      }

      $(this).prop("disabled", true).text("Verifying...")

      $.post(
        pfl_ajax.ajax_url,
        {
          action: "pfl_verify_code",
          email: email,
          code: code,
          nonce: pfl_ajax.nonce,
        },
        (response) => {
          //console.log(response);
          if (response.success) {
            isUserEmailVerified = true
            $("#email").prop("readonly", true)
            $("#user_verification_section").hide()
            showSuccessMessage("Email verified successfully")
            $('#verifyEmail').prop("disabled", true).text('Verified');
            $('.security-area').show();
          } else {
            showFieldError("user_verification_code", response.data)
          }
        },
      ).always(() => {
        $("#verifyUserCode").prop("disabled", false).text("Verified")
      })
    })

    $(".pfl-tab-button").on("click", function () {
      var tab = $(this).data("tab");

      // Button styles
      $(".pfl-tab-button")
        .removeClass("border-blue-500 text-blue-600")
        .addClass("border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300");

      $(this)
        .removeClass("border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300")
        .addClass("border-blue-500 text-blue-600");

      // Tab content
      $(".pfl-tab-content").addClass("hidden");
      $('.pfl-tab-content[data-tab="' + tab + '"]').removeClass("hidden");

      if (tab === "browse") {
        loadFlightRequests()
      } else if (tab === "loads") {
        loadRespondRequests()
      } 
    });

    $("#filter_airline, #filter_status").on("change", () => {
      loadFlightRequests()
    })

    $("#filter_status-loads").on("change", () => {
      loadRespondRequests()
    })

    $("#refresh_requests").on("click", () => {
      loadFlightRequests()
    })

    $("#refresh_requests-loads").on("click", () => {
      loadRespondRequests()
    })

    

    function showFieldError(fieldName, message) {
      clearFieldError(fieldName)
      var field = $('[name="' + fieldName + '"]')
      var errorHtml =
        '<div class="pfl-field-error mt-1 flex items-center text-sm text-red-600">' +
        '<svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">' +
        '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>' +
        "</svg>" +
        message +
        "</div>"
      field.closest(".pfl-form-group").append(errorHtml)
      field.addClass("!border-red-300 focus:border-red-500 focus:ring-red-500")
    }

    function clearFieldError(fieldName) {
      var field = $('[name="' + fieldName + '"]')
      field.removeClass("!border-red-300 focus:border-red-500 focus:ring-red-500")
      field.closest(".pfl-form-group").find(".pfl-field-error").remove()
    }

    function clearAllErrors() {
      $(".pfl-field-error").remove()
      $("input, select, textarea").removeClass("border-red-300 focus:border-red-500 focus:ring-red-500")
    }

    function showSuccessMessage(message) {
      var successHtml =
        '<div class="pfl-success-message mb-4 p-4 bg-green-50 border border-green-200 rounded-md">' +
        '<div class="flex items-center">' +
        '<svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">' +
        '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>' +
        "</svg>" +
        '<span class="text-green-800 font-medium">' +
        message +
        "</span>" +
        "</div></div>"
      $(".pfl-form-container").html(successHtml)
      $(".pfl-form-container").show();
      //setTimeout(() => $(".pfl-success-message").fadeOut(), 5000)
    }

    function initApplicationForm() {
      var currentStep = 1
      var maxSteps = 3
      var isEmailVerified = false
      var mainStatus = 'active';

      $('#airline_email').on('keyup', function(e){
        $('#send_verification').show();
      });

      // Status change handler
      $('input[name="status"]').on("change", function () {
        let status = $(this).val();
        mainStatus = status;
        let field = $('#additional_fields');
        let today = new Date();

        
        if (field.hasClass("hidden")) {
          field.removeClass("hidden");
        }

        if (status === "retired") {
            // Change labels
            $("#date_label").text("Retirement Date *");
            $("#job_label").text("Ex-Airline Job *");
            $("#upload_label").text("Upload Retired ID or a government-issued ID *");

            // Enable manual input
            $("#employment_retirement_date").prop("readonly", false).val("");
            $("#years_worked").prop("readonly", false).val("");
            $("#years_worked_toggle").show();

        } else {
            // Change labels
            $("#date_label").text("Employment Start Date *");
            $("#job_label").text("Airline Job *");
            $("#upload_label").text("Airline ID *");

            // Compute years worked
            let joinedYear = today.getFullYear(); // replace with real joined year if from DB
            let years = today.getFullYear() - joinedYear;
            $("#years_worked").val(years);
            $("#years_worked_toggle").hide();
        }
      });

      let today = new Date();
      let oneYearAgo = new Date();
      oneYearAgo.setFullYear(today.getFullYear() - 1);

      let formatDate = (date) => {
          let month = ("0" + (date.getMonth() + 1)).slice(-2);
          let day = ("0" + date.getDate()).slice(-2);
          return date.getFullYear() + "-" + month + "-" + day;
      };      

      // still restrict max to today (optional)
      $("#employment_retirement_date").attr("max", formatDate(today));

      $('#employment_retirement_date').on('change', function () {

          let joinedDate = new Date($(this).val());
          let today = new Date();

          if (!isNaN(joinedDate.getTime())) {
              let years = today.getFullYear() - joinedDate.getFullYear();
              let m = today.getMonth() - joinedDate.getMonth();
              let d = today.getDate() - joinedDate.getDate();

              // Adjust if not reached the month/day yet
              if (m < 0 || (m === 0 && d < 0)) {
                  years--;
              }

              if (mainStatus === "active"){
                $('#years_worked').val(years >= 0 ? years : 0);
                //console.log($('#years_worked').val());
              }
          }
      });


      // Step 1 Continue
      $("#step1_continue").on("click", () => {
        if (validateStep1()) {
          var status = $('input[name="status"]:checked').val()
          if (status === "active") {
            showStep(2)
          } else {
            showStep(3) // Skip email verification for retired users
          }
        }
      })

      // Send Verification Code
      $("#send_verification").on("click", function () {
        $('#verification_section').hide();
        var email = $("#airline_email").val()
        var airlineId = $("#airline_id").val()

        clearFieldError("airline_email")

        if (!email || !airlineId) {
          if (!email) showFieldError("airline_email", "Please enter your airline email address")
          if (!airlineId) showFieldError("airline_search", "Please select an airline first")
          return
        }

        $(this).prop("disabled", true).text("Sending...")

        $.post(
          pfl_ajax.ajax_url,
          {
            action: "pfl_send_verification",
            email: email,
            airline_id: airlineId,
            nonce: pfl_ajax.nonce,
          },
          (response) => {
            
            if (response.success) {
              $("#verification_section").show()
              showSuccessMessage("Verification code sent to your email")
            } else {
              showFieldError("airline_email", response.data)
            }
          },
        ).always(() => {
          $("#send_verification").prop("disabled", false).text("Send Verification Code")
        })
      })

      // Verify Code
      $("#verify_code").on("click", function () {
        var email = $("#airline_email").val()
        var code = $("#verification_code").val()

        clearFieldError("verification_code")

        if (!code) {
          showFieldError("verification_code", "Please enter the verification code")
          return
        }

        $(this).prop("disabled", true).text("Verifying...")

        $.post(
          pfl_ajax.ajax_url,
          {
            action: "pfl_verify_code",
            email: email,
            code: code,
            nonce: pfl_ajax.nonce,
          },
          (response) => {
            if (response.success) {
              isEmailVerified = true
              $("#airline_email").prop("readonly", true)
              $("#verification_section").hide()
              $("#step2_continue").show()
              showSuccessMessage("Email verified successfully. Click continue to proceed.")
              $('#send_verification').hide();
            } else {
              showFieldError("verification_code", response.data)
            }
          },
        ).always(() => {
          $("#verify_code").prop("disabled", false).text("Verify Code")
        })
      })

      // Step 2 Continue
      $("#step2_continue").on("click", () => {
        $(".pfl-form-container").hide();
        if (isEmailVerified) {
          showStep(3)
        }
      })

      // Form submission
      $("#pfl-application-form").on("submit", function (e) {
        e.preventDefault()

        if (!validateStep3()) {
          return
        }

        var formData = new FormData(this)
        formData.append("action", "pfl_submit_application")

        $('button[type="submit"]').prop("disabled", true).text("Submitting...")

        $.ajax({
          url: pfl_ajax.ajax_url,
          type: "POST",
          data: formData,
          processData: false,
          contentType: false,
          success: (response) => {
            //console.log(response);
            if (response.success) {
              showSuccessMessage("Application submitted successfully!")
              if (response.data.redirect_url) {
                setTimeout(() => (window.location.href = response.data.redirect_url), 2000)
              }
            } else {
              if (response.data.field_errors) {
                Object.keys(response.data.field_errors).forEach((field) => {
                  showFieldError(field, response.data.field_errors[field])
                })
              } else {
                showFieldError("username", response.data)
              }
            }
          },
          error: () => {
            showFieldError("username", "An error occurred. Please try again.")
          },
          complete: () => {
            $('button[type="submit"]').prop("disabled", false).text("Submit Application")
          },
        })
      })

      function showStep(step) {
        // Show/hide form steps (do NOT touch progress bar)
        $(".pfl-form-step").addClass("hidden").removeClass("block");
        $('.pfl-form-step[data-step="' + step + '"]').removeClass("hidden").addClass("block");

        // Update progress bar (scoped to #pfl-progress)
        $("#pfl-progress [data-step]").each(function () {
          const $pill = $(this);
          const n = parseInt($pill.attr("data-step"), 10);
          const $num = $pill.find("span").first(); // the number circle

          if (n === step) {
            // active
            $pill.removeClass("bg-gray-200 text-gray-600").addClass("bg-blue-500 text-white");
            $num.removeClass("bg-gray-400 text-white").addClass("bg-white text-blue-500");
          } else {
            // inactive
            $pill.removeClass("bg-blue-500 text-white").addClass("bg-gray-200 text-gray-600");
            $num.removeClass("bg-white text-blue-500").addClass("bg-gray-400 text-white");
          }
        });

        currentStep = step;
        clearAllErrors();
      }




      function validateStep1() {
        var isValid = true
        var requiredFields = ["full_name", "airline_id", "status"]
        clearAllErrors()

        requiredFields.forEach((field) => {
          var input = $('[name="' + field + '"]');
          var value;        
          if (input.attr("type") === "radio") {
            // Only get checked radio value
            value = $('[name="' + field + '"]:checked').val();
          } else {
            value = input.val();
          }
          if (!value) {
            if (field === "airline_id") {
              showFieldError("airline_search", "Please select an airline");
            } else {
              showFieldError(field, "This field is required");
            }
            isValid = false;
          }

        })

        // Validate retired fields if status is retired
        
        var retiredFields = ["phone_number", "employment_retirement_date", "airline_job", "years_worked", "upload_id"]
        retiredFields.forEach((field) => {
          var input = $('[name="' + field + '"]')
          if (!input.val()) {
            showFieldError(field, "This field is required")
            isValid = false
          }
        })
        

        return isValid
      }

      function validateStep3() {
        var password = $("#password").val()
        var confirmPassword = $("#confirm_password").val()
        var emailField = $("#email").val();
        var isValid = true

        var requiredFields = ["username", "email", "password", "confirm_password"]

        clearAllErrors()

        requiredFields.forEach((field)=>{
          var input = $('[name="' + field + '"]')
          if(!input.val()){
            showFieldError(field, "This field is required");
            isValid = false;
          }
        })

        if (password !== confirmPassword) {
          showFieldError("confirm_password", "Passwords do not match")
          isValid = false
        }

        if (password.length < 6) {
          showFieldError("password", "Password must be at least 6 characters long")
          isValid = false
        }

        if(emailField && !isUserEmailVerified){
          showFieldError("email", "Email must be verified");
          isValid = false
        } else{
          isValid = true;
        }

        return isValid
      }

    }

    function initFlightRequestForm() {
      // Return flight toggle
      $("#is_return").on("change", function () {
        if ($(this).is(":checked")) {
          $("#return_fields").show()
          // Auto-fill return flight data
          var outboundDate = $("#travel_date").val()
          if (outboundDate) {
            var returnDate = new Date(outboundDate)
            returnDate.setDate(returnDate.getDate() + 1)
            $("#return_travel_date").val(returnDate.toISOString().split("T")[0])
          }
        } else {
          $("#return_fields").hide()
        }
      })

      // Character counter for notes
      $("#notes").on("input", function () {
        var length = $(this).val().length
        $(".pfl-char-count").text(length + "/300 characters")

        if (length > 300) {
          showFieldError("notes", "Notes cannot exceed 300 characters")
        } else {
          clearFieldError("notes")
        }
      })

      // Date restrictions (yesterday, today, tomorrow only)
      var today = new Date()
      var yesterday = new Date(today)
      yesterday.setDate(yesterday.getDate() - 1)
      var threeYearsLater = new Date(today);
      threeYearsLater.setFullYear(threeYearsLater.getFullYear() + 3);

      $("#travel_date, #return_travel_date, #travel_date_search").attr({
        min: yesterday.toISOString().split("T")[0],
        max: threeYearsLater.toISOString().split("T")[0],
      })

      // Form submission
      $("#pfl-flight-request-form").on("submit", function (e) {
        e.preventDefault()
        $('.pfl-form-container').hide();

        if (!validateFlightRequest()) {
          return
        }

        var formData = $(this).serialize()
        
        // Decide which action to call
        if ($("#request_id").val()) {
          formData += "&action=pfl_update_flight_request";
        } else {
          formData += "&action=pfl_submit_flight_request";
        }

        $('button[type="submit"]').prop("disabled", true).text("Submitting...")

        $.post(pfl_ajax.ajax_url, formData, (response) => {
          console.log(response);
          if (response.success) {
                  let msg = $("#request_id").val()
              ? "Flight request updated successfully!"
              : "Flight request submitted successfully!";

            showSuccessMessage(msg);

            // Reset only if new, not when editing
            if (!$("#request_id").val()) {
              $("#pfl-flight-request-form")[0].reset();
              $("#reset-btn").click();
            }

            $(".pfl-suggestions").hide();
            clearAllErrors();
          } else {
            if (response.data.field_errors) {
              Object.keys(response.data.field_errors).forEach((field) => {
                showFieldError(field, response.data.field_errors[field])
              })
            } else {
              showFieldError("flight_number", response.data)
            }
          }
        }).always(() => {
          $('button[type="submit"]').prop("disabled", false).text("Submit Request")
        })
      })

      function validateFlightRequest() {
        var isValid = true
        var requiredFields = [
          "request_airline_name",
          "flight_number",
          "from_airport",
          "to_airport",
          "travel_date",
          "notes"
        ]

        if($('#is_return').is(":checked")){
          requiredFields.push(
            "return_airline",
            "return_flight_number",
            "return_travel_date",
          )          
        }

        clearAllErrors()

        requiredFields.forEach((field) => {
          var input = $('[name="' + field + '"]')
          if (!input.val()) {
            var fieldName = field.replace("_id", "").replace("_", " ")
            showFieldError(field, "This field is required")
            isValid = false
          }
        })

        // Validate flight number format
        var flightNumber = $('[name="flight_number"]').val()
        if (flightNumber && !/^\d{1,4}$/.test(flightNumber)) {
          showFieldError("flight_number", "Flight number must be 1-4 digits only")
          isValid = false
        }
        if($('#is_return').is(":checked")){
          var returnFlightNumber = $('[name="return_flight_number"]').val()
          //console.log(returnFlightNumber);
          if (returnFlightNumber && !/^\d{1,4}$/.test(returnFlightNumber)) {
            showFieldError("return_flight_number", "Flight number must be 1-4 digits only")
            isValid = false
          }
        }

        return isValid
      }

      
    }

    function initPasswordForm() {
      $("#pfl-password-form").on("submit", function (e) {
        e.preventDefault()

        if (!validatePasswordForm()) {
          return
        }

        var formData = $(this).serialize()
        formData += "&action=pfl_update_password"

        $('button[type="submit"]').prop("disabled", true).text("Updating...")

        $.post(pfl_ajax.ajax_url, formData, (response) => {
          if (response.success) {
            showSuccessMessage("Password updated successfully!")
            $("#pfl-password-form")[0].reset()
            clearAllErrors()
          } else {
            if (response.data.field_errors) {
              Object.keys(response.data.field_errors).forEach((field) => {
                showFieldError(field, response.data.field_errors[field])
              })
            } else {
              showFieldError("current_password", response.data)
            }
          }
        }).always(() => {
          $('button[type="submit"]').prop("disabled", false).text("Update Password")
        })
      })

      function validatePasswordForm() {
        var currentPassword = $('[name="current_password"]').val()
        var newPassword = $('[name="new_password"]').val()
        var confirmPassword = $('[name="confirm_password"]').val()
        var isValid = true

        clearAllErrors()

        if (!currentPassword) {
          showFieldError("current_password", "Current password is required")
          isValid = false
        }

        if (!newPassword) {
          showFieldError("new_password", "New password is required")
          isValid = false
        } else if (newPassword.length < 6) {
          showFieldError("new_password", "Password must be at least 6 characters long")
          isValid = false
        }

        if (newPassword !== confirmPassword) {
          showFieldError("confirm_password", "Passwords do not match")
          isValid = false
        }

        return isValid
      }
    }

    function initAirlineAutocomplete(inputSelector, hiddenSelector, suggestionsSelector) {
      //console.log(allAirlines);
      $(inputSelector).on("input", function () {
        let query = $(this).val().toLowerCase();
        let $suggestions = $(suggestionsSelector);

        if (query.length < 2) {
          $suggestions.hide();
          return;
        }

        // Filter from preloaded list
        let filtered = allAirlines.filter(a =>
          a.name.toLowerCase().includes(query) ||
          a.iata_code.toLowerCase().includes(query) ||
          a.domain.toLowerCase().includes(query)
        );

        if (filtered.length > 0) {
          let html = filtered.map(a =>
            `<div class="pfl-suggestion-item p-2 border text-sm" data-id="${a.id}" data-name="${a.name}" data-domain="${a.domain}" data-iata="${a.iata_code}">
              <strong>${a.name}</strong> (${a.iata_code}) - ${a.domain}
            </div>`
          ).join("");

          $suggestions.html(html).show();
        } else {
          $suggestions.hide();
        }
      });

      $(document).on("click", suggestionsSelector + " .pfl-suggestion-item", function () {
        let id = $(this).data("id");
        let iata = $(this).data('iata');
        let name = $(this).data("name");
        let domain = $(this).data("domain");

        $(inputSelector).val(name+' ('+domain+')');
        $(hiddenSelector).val(id);
        $('#airline_code').val(iata);
        $(suggestionsSelector).hide();
      });
    }

    // Autocomplete logic
    function initAirportAutocomplete(inputSelector, hiddenSelector, suggestionsSelector) {
      $(inputSelector).on("input", function () {
        
        const query = $(this).val().toLowerCase();
        let $suggestions = $(suggestionsSelector);
        if (!query) {
          $suggestions.hide();
        }

        const results = allAirports.filter(a =>
          a.name.toLowerCase().includes(query) ||
          a.iata_code.toLowerCase().includes(query) ||
          a.country.toLowerCase().includes(query)
          ).slice(0, 10);

        if (results.length === 0) {
          $suggestions.hide()
          return;
        }

        let html = "";
        results.forEach(a => {
          html += `<div class="airport-item p-2 border text-sm cursor-pointer hover:bg-gray-200"
                    data-id="${a.id}" data-iata="${a.iata_code}">
                    ${a.name} (${a.iata_code}) - ${a.country}
        </div>`;
      });

        $suggestions.html(html).show();
      });

      // On select
      $(document).on("click", suggestionsSelector + " .airport-item", function () {
        const id = $(this).data("id");
        const text = $(this).data("iata");
        
        $(inputSelector).val(text);
        $(hiddenSelector).val(id);
        $(suggestionsSelector).hide()
      });

    }

  })

  //START FLIGHT LOAD REQUESTS
  function loadFlightRequests() {
    
    //var filterAirline = $("#filter_airline").val()
    var filterStatus = $("#filter_status").val()

    $("#pfl-requests-list").html(
      '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-500">Loading requests...</p></div>',
    )

    $.get(
      pfl_ajax.ajax_url,
      {
        action: "pfl_get_flight_requests",
        filter_status: filterStatus,
        nonce: pfl_ajax.nonce,
      },
      (response) => {
        //console.log(response);
        //console.log(filterStatus);
        if (response.success) {
          displayFlightRequests(response.data, 'pfl-requests-list')
          //populateAirlineFilter(response.data.airlines)
        } else {
          $("#pfl-requests-list").html(
            '<div class="text-center py-8 text-gray-500">No requests found or error loading requests.</div>',
          )
        }
      },
    )
  }

  function loadRespondRequests() {

    var filterStatus = $("#filter_status-loads").val()
    //console.log('response');
    // Implementation for loading requests that user can respond to
    $("#pfl-requests-list-loads").html(
      '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-500">Loading requests...</p></div>',
    )

    $.get(
      pfl_ajax.ajax_url,
      {
        action: "pfl_get_respond_requests",
        filter_status: filterStatus,
        nonce: pfl_ajax.nonce,
      },
      (response) => {
        //console.log(response);
        //console.log(response);
        //console.log(filterStatus);
        if (response.success) {
          displayFlightRequests(response.data, 'pfl-requests-list-loads')
          //populateAirlineFilter(response.data.airlines)
        } else {
          $("#pfl-requests-list-loads").html(
            '<div class="text-center py-8 text-gray-500">No requests found or error loading requests.</div>',
          )
        }
        
      },
    )
  } //END FLIGHT LOAD REQUESTS

  function displayFlightRequests(requests, container) {

    var html = ""

    if (requests.length === 0) {
      html = '<div class="text-center py-8 text-gray-500">No flight requests found.</div>'
    } else {
      requests.forEach((request) => {
        let isAuthor = request.currentUser === request.user_id ? true : false ;
        html +=
          '<div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">'
        html += '<div class="flex justify-between items-start mb-4">'
        html += "<div>"
        html +=
          '<h4 class="text-lg font-semibold text-gray-900">' +
          request.airline_code +
          " " +
          request.flight_number +
          "</h4>"
        html +=
          '<p class="text-gray-600">' +
          formatDate(request.travel_date) +
          " • " +
          request.from_airport_id +
          " → " +
          request.to_airport_id +
          "</p>"
        if (request.return_flight_number) {
          html +=
            '<p class="text-gray-600 text-sm">Return: ' +
            request.return_airline_code +
            " " +
            request.return_flight_number +
            " • " +
            formatDate(request.return_travel_date) +
            "</p>"
        }
        html += "</div>"
        html += '<div class="flex items-center space-x-2">'
        html +=
          '<span class="px-2 py-1 text-xs font-medium rounded-full ' +
          getStatusBadgeClass(request.status) +
          '">' +
          request.status.charAt(0).toUpperCase() +
          request.status.slice(1) +
          "</span>"
        html += '<span class="text-xs text-gray-500">' + timeAgo(request.created_at) + "</span>"
        html += "</div>"
        html += "</div>"

        if (request.notes) {
          html += '<div class="mb-4 p-3 bg-gray-50 rounded-md">'
          html += '<p class="text-sm text-gray-700">' + escapeHtml(request.notes) + "</p>"
          html += "</div>"
        }

        html += '<div class="border-t pt-4">'
        html += '<div class="flex items-center justify-between mb-3">'
        html += '<div class="flex items-center space-x-4">'
        html +=
          '<button class="pfl-like-btn flex items-center space-x-1 text-sm ' +
          (request.user_liked ? "text-red-600" : "text-gray-500 hover:text-red-600") +
          '" data-request-id="' +
          request.id +
          '">'
        html +=
          '<svg class="w-4 h-4 ' +
          (request.user_liked ? "fill-current" : "") +
          '" fill="' +
          (request.user_liked ? "currentColor" : "none") +
          '" stroke="currentColor" viewBox="0 0 24 24">'
        html +=
          '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>'
        html += "</svg>"
        html += '<span class="pfl-like-count">' + request.like_count + "</span>"
        html += "</button>"
        html +=
          '<button class="pfl-comment-toggle flex items-center space-x-1 text-sm text-gray-500 hover:text-blue-600" data-request-id="' +
          request.id +
          '">'
        html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
        html +=
          '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a9.863 9.863 0 01-4.255-.949L5 20l1.395-3.72C5.512 15.042 5 13.574 5 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>'
        html += "</svg>"
        html += "<span>" + request.comment_count + " comments</span>"
        html += "</button>"
        html += "</div>"
        if (request.status === "pending" && !isAuthor) {
          html += '<div class="flex items-center">'
          html +=
            '<button class="pfl-give-load-btn bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-request-id="' +
            request.id +
            '">'
          html += "Give Flight Load"
          html += "</button>"
          html += "</div>"
        }
        html += "</div>"

        // Comments section
        html += '<div class="pfl-comments-section hidden" data-request-id="' + request.id + '">'
        html += '<div class="pfl-comments-list space-y-3 mb-4"></div>'
        html += '<div class="flex space-x-3">'
        html += '<div class="flex-1">'
        html +=
          '<textarea class="pfl-comment-input w-full px-3 py-2 border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" rows="2" placeholder="Add a comment..." data-request-id="' +
          request.id +
          '"></textarea>'
        html += "</div>"
        html +=
          '<button class="pfl-comment-submit bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50" data-request-id="' +
          request.id +
          '">Post</button>'
        html += "</div>"
        html += "</div>"
        html += "</div>"
        html += "</div>"
      })
    }

    $(`#${container}`).html(html);
  }

  $(document).on("click", ".pfl-like-btn", function () {
    
    var $btn = $(this)
    var requestId = $btn.data("request-id")
    var isLiked = $btn.hasClass("text-red-600")

    $btn.prop("disabled", true)

    $.post(
      pfl_ajax.ajax_url,
      {
        action: "pfl_toggle_like",
        request_id: requestId,
        nonce: pfl_ajax.nonce,
      },
      (response) => {
        
        if (response.success) {
          var $count = $btn.find(".pfl-like-count")
          var $icon = $btn.find("svg")


          if (response.data.liked) {
            $btn.removeClass("text-gray-500 hover:text-red-600").addClass("text-red-600")
            $icon.addClass("fill-current fill-blue-600 text-blue-600").attr("fill", "currentColor")
          } else {
            $btn.removeClass("text-red-600").addClass("text-gray-500 hover:text-red-600")
            $icon.addClass("fill-current fill-blue-600 text-blue-600").attr("fill", "currentColor")
          }

          $count.text(response.data.like_count)
        }
      },
    ).always(() => {
      $btn.prop("disabled", false)
    })
  })

  $(document).on("click", ".pfl-comment-toggle", function () {
    var requestId = $(this).data("request-id")
    var $commentsSection = $('.pfl-comments-section[data-request-id="' + requestId + '"]')

    if ($commentsSection.hasClass("hidden")) {
      $commentsSection.removeClass("hidden")
      loadComments(requestId)
    } else {
      $commentsSection.addClass("hidden")
    }
  })

  $(document).on("click", ".pfl-comment-submit", function () {
    var $btn = $(this)
    var requestId = $btn.data("request-id")
    var $textarea = $('.pfl-comment-input[data-request-id="' + requestId + '"]')
    var comment = $textarea.val().trim()

    if (!comment) {
      return
    }

    $btn.prop("disabled", true).text("Posting...")

    $.post(
      pfl_ajax.ajax_url,
      {
        action: "pfl_add_comment",
        request_id: requestId,
        comment: comment,
        nonce: pfl_ajax.nonce,
      },
      (response) => {
        if (response.success) {
          $textarea.val("")
          loadComments(requestId)
          // Update comment count
          $('.pfl-comment-toggle[data-request-id="' + requestId + '"] span').text(
            response.data.comment_count + " comments",
          )
        }
      },
    ).always(() => {
      $btn.prop("disabled", false).text("Post")
    })
  })

  function loadComments(requestId) {
    var $commentsList = $('.pfl-comments-list[data-request-id="' + requestId + '"]')
    var $special = $('.pfl-comments-list').data();
    if (!$commentsList.length) {
      $commentsList = $('.pfl-comments-section[data-request-id="' + requestId + '"] .pfl-comments-list')
    }

    $.get(
      pfl_ajax.ajax_url,
      {
        action: "pfl_get_comments",
        request_id: requestId,
        nonce: pfl_ajax.nonce,
      },
      (response) => {
        //console.log(response);
        if (response.success) {
          var html = ""
          if($special){
            response.data.forEach((comment)=> {
              html += '<div class="flex items-start space-x-2 mb-2">'
              html += '<div class="w-8 h-8 rounded-full bg-gray-200"></div>'
              html += '<div class="bg-gray-100 rounded-lg px-3 py-2">'
              html += '<p class="text-sm"><span class="font-semibold">'+ escapeHtml(comment.author_name)+':</span> '+escapeHtml(comment.comment)+'</p>'
              html += '</div></div>'
            })
          }
          else{
            response.data.forEach((comment) => {
              html += '<div class="flex space-x-3 p-3 bg-gray-50 rounded-md">'
              html += '<div class="flex-1">'
              html += '<div class="flex items-center space-x-2 mb-1">'
              html += '<span class="text-sm font-medium text-gray-900">' + escapeHtml(comment.author_name) + "</span>"
              html += '<span class="text-xs text-gray-500">' + timeAgo(comment.created_at) + "</span>"
              html += "</div>"
              html += '<p class="text-sm text-gray-700">' + escapeHtml(comment.comment) + "</p>"
              html += "</div>"
              html += "</div>"
            })
          }
          $commentsList.html(html)
        }
      },
    )
  }

  function populateAirlineFilter(airlines) {
    var $filter = $("#filter_airline")
    var currentValue = $filter.val()

    $filter.find("option:not(:first)").remove()

    airlines.forEach((airline) => {
      $filter.append('<option value="' + airline.id + '">' + airline.name + "</option>")
    })

    $filter.val(currentValue)
  }

  function getStatusBadgeClass(status) {
    switch (status) {
      case "pending":
        return "bg-yellow-100 text-yellow-800"
      case "answered":
        return "bg-green-100 text-green-800"
      case "expired":
        return "bg-red-100 text-red-800"
      default:
        return "bg-gray-100 text-gray-800"
    }
  }

  function formatDate(dateString) {
    var date = new Date(dateString)
    return date.toLocaleDateString("en-US", {
      weekday: "short",
      year: "numeric",
      month: "short",
      day: "numeric",
    })
  }

  function timeAgo(dateString) {
    var date = new Date(dateString)
    var now = new Date()
    var diffInSeconds = Math.floor((now - date) / 1000)

    if (diffInSeconds < 60) return "just now"
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + "m ago"
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + "h ago"
    return Math.floor(diffInSeconds / 86400) + "d ago"
  }

  function escapeHtml(text) {
    var div = document.createElement("div")
    div.textContent = text
    return div.innerHTML
  }


  function preloadData() {
    disableInput("#from_airport_search", "Loading airports...");
    disableInput("#to_airport_search", "Loading airports...");
    disableInput("#request_airline", "Loading airlines...");
    disableInput("#return_airline", "Loading airlines...");
    disableInput("#request_airline_name", "Loading airlines...");

    $.when(loadAirports(), loadAirlines()).done(function () {

        
        enableInput("#from_airport_search", "Search airport...");
        enableInput("#to_airport_search", "Search airport...");
        enableInput("#request_airline", "Search airline...");
        enableInput("#return_airline", "Search airline...");
        enableInput("#request_airline_name", "Search airline...");

        // Init autocomplete after data ready
        initAutocomplete("#from_airport_search", "#from_airport_id_search", "#from_airport_suggestions", allAirports);
        initAutocomplete("#to_airport_search", "#to_airport_id_search", "#to_airport_suggestions", allAirports);
        initAutocomplete("#request_airline", "#request_airline_code", "#request_airline_suggestions", allAirlinesPublic);
        initAutocomplete("#return_airline", "#return_airline_code", "#return_airline_suggestions", allAirlinesPublic);
        initAutocomplete("#request_airline_name", "#request_airline_code", "#request_airline_suggestions", allAirlinesPublic);
    });
  }


  // ====== Load Airports ======
  function loadAirports() {
      let dfd = $.Deferred();
      let cache = localStorage.getItem("airports_data_" + AIRPORTS_VERSION);
      if (cache) {
          allAirports = JSON.parse(cache);
          dfd.resolve();
      } else {
          $.get(pfl_ajax.ajax_url, { action: "pfl_get_all_airports" }, function (response) {
              if (response.success) {
                  allAirports = response.data;
                  localStorage.setItem("airports_data_" + AIRPORTS_VERSION, JSON.stringify(allAirports));
              }
              dfd.resolve();
          });
      }
      return dfd.promise();
  }


  // ====== Load Airlines ======
  function loadAirlines() {
      let dfd = $.Deferred();
      let cache = localStorage.getItem("airlines_data_" + AIRLINES_VERSION);
      if (cache) {
          allAirlinesPublic = JSON.parse(cache);
          dfd.resolve();
      } else {
          $.get(pfl_ajax.ajax_url, { action: "pfl_get_all_airlines_xml" }, function (response) {
              if (response.success) {
                  allAirlinesPublic = response.data;
                  localStorage.setItem("airlines_data_" + AIRLINES_VERSION, JSON.stringify(allAirlinesPublic));
              }
              dfd.resolve();
          });
      }
      return dfd.promise();
  }


  // ====== Input State Helpers ======
  function disableInput(selector, placeholder) {
      $(selector).prop("disabled", true).attr("placeholder", placeholder);
  }

  function enableInput(selector, placeholder) {
      $(selector).prop("disabled", false).attr("placeholder", placeholder);
  }


  // ====== Autocomplete ======
  function initAutocomplete(inputSel, hiddenSel, suggestionSel, dataList) {
      let $input = $(inputSel);
      let $hidden = $(hiddenSel);
      let $suggestionBox = $(suggestionSel);

      function filterData(query) {
          query = query.trim().toLowerCase().replace(/\s+/g, '');

          if (!query) return [];

          // Split into two arrays: code matches first, then name matches
          let codeMatches = [];
          let nameMatches = [];

          for (let item of dataList) {
              let code = item.code ? item.code.trim().toLowerCase() : '';
              let name = item.name ? item.name.trim().toLowerCase().replace(/\s+/g, '') : '';

              if (code.includes(query)) codeMatches.push(item);
              else if (name.includes(query)) nameMatches.push(item);
          }

          return codeMatches.concat(nameMatches).slice(0, 10);
      }

      function renderSuggestions(filtered) {
          $suggestionBox.empty();
          filtered.forEach(item => {
              $("<div>")
                  .text(`${item.name} (${item.code})`)
                  .addClass("px-3 text-sm border py-2 hover:bg-gray-100 cursor-pointer")
                  .on("click", function () {
                      $input.val(item.type == 'airport' ? item.code : item.name);
                      $hidden.val(item.code);
                      $suggestionBox.addClass("hidden");
                  })
                  .appendTo($suggestionBox);
          });

          if (filtered.length > 0) $suggestionBox.removeClass("hidden");
          else $suggestionBox.addClass("hidden");
      }

      $input.on("input", function () {
          let query = $(this).val();
          let filtered = filterData(query);
          renderSuggestions(filtered);
      });

      // Hide suggestions when input loses focus or pressing Tab
      $input.on("blur keydown", function (e) {
          if (e.type === "blur" || e.key === "Tab") {
              setTimeout(() => $suggestionBox.addClass("hidden"), 100);
          }
      });
  }


  $(document).on("click", ".pfl-give-load-btn, .flightload-btn", function () {
    var requestId = $(this).data("request-id")
    showGiveFlightLoadModal(requestId)
  })

  function showGiveFlightLoadModal(requestId) {
    var modalHtml = `
      <div id="pfl-flight-load-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full z-999999999">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
          <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900">Give Flight Load Information</h3>
              <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            
            <div class="mb-4 p-3 bg-blue-50 rounded-md">
              <p class="text-sm text-blue-800">
                <strong>Instructions:</strong> Fill in the appropriate fields and/or place any additional information in the Notes section.
              </p>
            </div>
            
            <form id="pfl-flight-load-form" data-request-id="${requestId}">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Class Types</label>
                  <div class="space-y-3">
                    <div class="flex items-center">
                      <input type="checkbox" id="first_class" name="class_types[]" value="first_class" class="pfl-class-checkbox mr-2">
                      <label for="first_class" class="text-sm text-gray-700">First Class</label>
                    </div>
                    <div id="first_class_fields" class="ml-6 space-y-2 hidden">
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div>
                          <label class="block text-xs text-gray-600">Booked</label>
                          <select name="first_class_booked" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            <option value="">Select</option>
                            <option value="At Authorization">At Authorization</option>
                            <option value="Overbooked">Overbooked</option>
                            <option value="Slightly Overbooked">Slightly Overbooked</option>
                            <option value="At Capacity">At Capacity</option>
                            <option value="Below Capacity">Below Capacity</option>
                            <option value="Wide Open">Wide Open</option>
                          </select>
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Cap</label>
                          <input type="number" name="first_class_cap" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Held</label>
                          <input type="number" name="first_class_held" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Standbys</label>
                          <input type="number" name="first_class_standbys" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex items-center">
                      <input type="checkbox" id="business_class" name="class_types[]" value="business_class" class="pfl-class-checkbox mr-2">
                      <label for="business_class" class="text-sm text-gray-700">Business Class</label>
                    </div>
                    <div id="business_class_fields" class="ml-6 space-y-2 hidden">
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div>
                          <label class="block text-xs text-gray-600">Booked</label>
                          <select name="business_class_booked" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            <option value="">Select</option>
                            <option value="At Authorization">At Authorization</option>
                            <option value="Overbooked">Overbooked</option>
                            <option value="Slightly Overbooked">Slightly Overbooked</option>
                            <option value="At Capacity">At Capacity</option>
                            <option value="Below Capacity">Below Capacity</option>
                            <option value="Wide Open">Wide Open</option>
                          </select>
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Cap</label>
                          <input type="number" name="business_class_cap" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Held</label>
                          <input type="number" name="business_class_held" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Standbys</label>
                          <input type="number" name="business_class_standbys" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex items-center">
                      <input type="checkbox" id="premium_economy_class" name="class_types[]" value="premium_economy_class" class="pfl-class-checkbox mr-2">
                      <label for="premium_economy_class" class="text-sm text-gray-700">Premium Economy Class</label>
                    </div>
                    <div id="premium_economy_class_fields" class="ml-6 space-y-2 hidden">
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div>
                          <label class="block text-xs text-gray-600">Booked</label>
                          <select name="premium_economy_class_booked" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            <option value="">Select</option>
                            <option value="At Authorization">At Authorization</option>
                            <option value="Overbooked">Overbooked</option>
                            <option value="Slightly Overbooked">Slightly Overbooked</option>
                            <option value="At Capacity">At Capacity</option>
                            <option value="Below Capacity">Below Capacity</option>
                            <option value="Wide Open">Wide Open</option>
                          </select>
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Cap</label>
                          <input type="number" name="premium_economy_class_cap" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Held</label>
                          <input type="number" name="premium_economy_class_held" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Standbys</label>
                          <input type="number" name="premium_economy_class_standbys" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex items-center">
                      <input type="checkbox" id="economy_class" name="class_types[]" value="economy_class" class="pfl-class-checkbox mr-2">
                      <label for="economy_class" class="text-sm text-gray-700">Economy Class</label>
                    </div>
                    <div id="economy_class_fields" class="ml-6 space-y-2 hidden">
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div>
                          <label class="block text-xs text-gray-600">Booked</label>
                          <select name="economy_class_booked" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            <option value="">Select</option>
                            <option value="At Authorization">At Authorization</option>
                            <option value="Overbooked">Overbooked</option>
                            <option value="Slightly Overbooked">Slightly Overbooked</option>
                            <option value="At Capacity">At Capacity</option>
                            <option value="Below Capacity">Below Capacity</option>
                            <option value="Wide Open">Wide Open</option>
                          </select>
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Cap</label>
                          <input type="number" name="economy_class_cap" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Held</label>
                          <input type="number" name="economy_class_held" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                        <div>
                          <label class="block text-xs text-gray-600">Standbys</label>
                          <input type="number" name="economy_class_standbys" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" min="0">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div>
                  <label for="flight_load_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                  <textarea id="flight_load_notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Additional information..."></textarea>
                </div>
              </div>
              
              <div class="flex justify-end space-x-3 mt-6">
                <button type="button" id="cancel-flight-load" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                  Cancel
                </button>
                <input type="hidden" name="request_id" value="${requestId}"">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                  Submit Flight Load
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    `

    $("body").append(modalHtml)

    $(".pfl-class-checkbox").on("change", function () {
      var classType = $(this).val()
      var fieldsDiv = $("#" + classType + "_fields") // Adjusted to remove underscores

      if ($(this).is(":checked")) {
        fieldsDiv.removeClass("hidden")
      } else {
        fieldsDiv.addClass("hidden")
        // Clear fields when unchecked
        fieldsDiv.find("input, select").val("")
      }
    })

    $("#close-modal, #cancel-flight-load").on("click", () => {
      $("#pfl-flight-load-modal").remove()
    })

    $("#pfl-flight-load-form").on("submit", function (e) {
      e.preventDefault()

      var formData = $(this).serialize()
      formData += "&action=pfl_submit_flight_load&nonce=" + window.pfl_ajax.nonce // Declare pfl_ajax variable

      $('#pfl-submit-request-btn').prop("disabled", true).text("Submitting...")

      $.post(window.pfl_ajax.ajax_url, formData, (response) => {
        console.log(response);
        // Declare pfl_ajax variable
        if (response.success) {
          if($("#pfl-flight-load-modal")){
            $("#pfl-flight-load-modal").remove()  
          }
          
          alert("Flight load information submitted successfully!") // Declare showSuccessMessage function
          //loadFlightRequests() // Declare loadFlightRequests function
        } else {
          alert("Error: " + response.data)
        }
      }).always(() => {
        $('#pfl-submit-request-btn').prop("disabled", false).text("Submit Flight Load")
      })
    })
  }

  $(".pfl-flight-load-request-form").on("submit", function (e) {
    e.preventDefault()

    var formData = $(this).serialize()
    formData += "&action=pfl_submit_flight_load&nonce=" + window.pfl_ajax.nonce // Declare pfl_ajax variable

    $('button[type="submit"]').prop("disabled", true).text("Submitting...")

    $.post(window.pfl_ajax.ajax_url, formData, (response) => {
      console.log(response);
      // Declare pfl_ajax variable
      if (response.success) {
        if($("#pfl-flight-load-modal")){
          $("#pfl-flight-load-modal").remove()  
        }
        
        alert("Flight load information submitted successfully!") // Declare showSuccessMessage function
        location.reload();
      } else {
        alert("Error: " + response.data)
      }
    }).always(() => {
      $('button[type="submit"]').prop("disabled", false).text("Submit Flight Load")
    })
  })

})(jQuery);

// Cabin checkbox listener
document.addEventListener("change", (e) => {
  if (e.target.classList.contains("cabin-toggle")) {
    const reqId = e.target.dataset.request;
    const container = document.getElementById("cabin-sections-"+reqId);
    const cabinKey = e.target.dataset.cabin;
    const cabinId = `cabin-${cabinKey}`;

    if (e.target.checked) {
      // Add section
      container.insertAdjacentHTML("beforeend", getCabinSection(cabinKey, cabinId));
    } else {
      // Remove section
      document.getElementById(cabinId)?.remove();
    }
  }
});

// Template for each cabin section
function getCabinSection(cabinKey, cabinId) {
  const labels = {
    first_class: "First Class",
    business_class: "Business Class",
    premium_economy_class: "Premium Economy Class",
    economy_class: "Economy Class",
  };

  return `
    <div id="${cabinId}" class="border p-3 rounded-lg bg-white shadow-sm">
      <h5 class="font-semibold mb-2">${labels[cabinKey]}</h5>
      <div class="grid grid-cols-2 gap-4">
        <!-- Booked Dropdown -->
        <div>
          <label class="block text-sm text-gray-600 mb-1">Booked</label>
          <select name="${cabinKey}_booked" 
            class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            <option value="">Select</option>
            <option value="At Authorization">At Authorization</option>
            <option value="Overbooked">Overbooked</option>
            <option value="Slightly Overbooked">Slightly Overbooked</option>
            <option value="At Capacity">At Capacity</option>
            <option value="Below Capacity">Below Capacity</option>
            <option value="Wide Open">Wide Open</option>
          </select>
        </div>
        <!-- Cap -->
        <div>
          <label class="block text-sm text-gray-600 mb-1">Cap</label>
          <input type="number" name="${cabinKey}_cap" 
            class="w-full px-2 py-1 border border-gray-300 rounded text-sm" />
        </div>
        <!-- Held -->
        <div>
          <label class="block text-sm text-gray-600 mb-1">Held</label>
          <input type="number" name="${cabinKey}_held" 
            class="w-full px-2 py-1 border border-gray-300 rounded text-sm" />
        </div>
        <!-- Standby -->
        <div>
          <label class="block text-sm text-gray-600 mb-1">Standby</label>
          <input type="number" name="${cabinKey}_standby" 
            class="w-full px-2 py-1 border border-gray-300 rounded text-sm" />
        </div>
      </div>
    </div>
  `;
}

document.addEventListener("DOMContentLoaded", function () {
  const dropdown = document.getElementById("notification-dropdown");
  const list = document.getElementById("notifications-list");
  const btn = document.getElementById("notification-btn");
  const countEl = document.getElementById("notification-count");

  // Toggle dropdown
  btn.addEventListener("click", () => {
    dropdown.classList.toggle("hidden");
  });

  // Function to fetch notifications
  function fetchNotifications() {
    fetch(pfl_ajax.rest_url, {
      headers: {
        "X-WP-Nonce": pfl_ajax.rest_nonce
      },
      credentials: "same-origin"
    })
      .then(res => res.json())
      .then(data => {
        //console.log("🔔 Notifications:", data);

        // Update count
        countEl.textContent = data.count;

        // Render notifications list
        list.innerHTML = "";
        if (data.items.length === 0) {
          list.innerHTML = `<li class="px-4 py-2 text-sm text-gray-500">No notifications yet</li>`;
        } else {
          data.items.forEach(item => {
            const li = document.createElement("li");
            li.className = "px-4 py-2 text-sm text-gray-700 hover:bg-gray-100";
            li.innerHTML = `
              <div class="font-medium">${item.message}</div>
              <div class="text-xs text-gray-400">${item.created_at}</div>
            `;
            list.appendChild(li);
          });
        }
      })
      .catch(err => console.error("Fetch error:", err))
      .finally(() => {
        // Schedule next poll after 10 seconds
        setTimeout(fetchNotifications, 10000);
      });
  }

  // Initial fetch
  fetchNotifications();
});


function markAsRead(id) {
  fetch(pfl_ajax.rest_url + '/read', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': pfl_ajax.rest_nonce
    },
    body: JSON.stringify({ id })
  }).then(r => r.json()).then(res => {
    if (res.success) {
      document.querySelector(`#notif-${id}`).remove();
    }
  });
}

document.getElementById('clear-all').addEventListener('click', () => {
  fetch(pfl_ajax.rest_url + '/clear', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': pfl_ajax.rest_nonce
    },

  }).then(r => r.json()).then(res => {
    if (res.success) {
      document.getElementById('notifications-list').innerHTML = '<li class="px-4 py-2 text-sm text-gray-500">No notifications yet</li>';
    }
  });
});


const scroller = document.getElementById("flightScroller");
document.getElementById("scrollLeft").addEventListener("click", (e) => {
  e.preventDefault();
  scroller.scrollBy({ left: -250, behavior: "smooth" });
});
document.getElementById("scrollRight").addEventListener("click", (e) => {
  e.preventDefault();
  scroller.scrollBy({ left: 250, behavior: "smooth" });
});


function formatDateTime(dateTimeStr) {
  // Example: "2025-08-31T01:45:00"
  const date = new Date(dateTimeStr);
  return date.toLocaleString("en-US", {
    month: "short",   // e.g. Aug
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true
  });
}

function formatDuration(durationStr) {
  // Example: "PT2H25M"
  // Remove "PT", split into hours/minutes
  const regex = /PT(?:(\d+)H)?(?:(\d+)M)?/;
  const match = durationStr.match(regex);
  if (!match) return durationStr;

  const hours = match[1] ? `${match[1]}h ` : "";
  const minutes = match[2] ? `${match[2]}m` : "";
  return (hours + minutes).trim();
}

function selectFlight(airline, airline_code ,number, from, to, date, aircraft) {
      jQuery("#search_fields").hide();
      jQuery("#request-fields").show();
      jQuery("#request_airline_name").val(airline);
      jQuery("#request_airline_code").val(airline_code);
      jQuery("#flight_number").val(number);
      jQuery("#from_airport").val(from);
      jQuery("#from_airport_id").val(from);
      jQuery("#to_airport").val(to);
      jQuery("#to_airport_id").val(to);
      jQuery("#aircraft").val(aircraft);
      jQuery("#travel_date").val(formatDate(date));
}

function formatDate(dateString) {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}