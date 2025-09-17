jQuery(document).ready(($) => {
  // Declare variables before using them
  var pfl_admin_ajax = window.pfl_admin_ajax || {} // Assuming pfl_admin_ajax is defined globally

  // Handle user approval
  $(".pfl-approve-user").on("click", function (e) {
    e.preventDefault()

    var userId = $(this).data("user-id")
    var button = $(this)

    $.ajax({
      url: pfl_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "pfl_approve_user",
        user_id: userId,
        nonce: pfl_admin_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          button
            .closest("tr")
            .find(".pfl-status")
            .text("Approved")
            .removeClass("pfl-status-pending")
            .addClass("pfl-status-approved")
          button.parent().html('<span class="pfl-status-approved">Approved</span>')
          alert("User approved successfully!")
          location.reload();
        } else {
          alert("Error: " + response.data)
        }
      },
      error: () => {
        alert("An error occurred. Please try again.")
      },
    })
  })

  $("#select-all").on("change", function () {
    $(".user-checkbox").prop("checked", this.checked);
  });

  $(document).on("change", ".user-checkbox", function () {
    if (!this.checked) {
      $("#select-all").prop("checked", false);
    } else if ($(".user-checkbox:checked").length === $(".user-checkbox").length) {
      $("#select-all").prop("checked", true);
    }
  });

  $("#bulk-apply").on("click", function (e) {
    e.preventDefault();

    var action = $("#bulk-action-selector-top").val();
    if (action === "-1") {
      alert("Please select a bulk action.");
      return;
    }

    // grab checkboxes anywhere on page
    var userIds = $(".user-checkbox:checked")
      .map(function () {
        return $(this).val();
      })
      .get();

    if (userIds.length === 0) {
      alert("Please select at least one user.");
      return;
    }

    $.ajax({
      url: pfl_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "pfl_bulk_user_action",
        user_ids: userIds,
        bulk_action: action,
        nonce: pfl_admin_ajax.nonce,
      },
      success: function (response) {
        console.log(response);
        if (response.success) {
          response.data.updated.forEach(function (id) {
            var row = $('input[value="' + id + '"]').closest("tr");
            if (action === "approve") {
              row.find(".pfl-status")
                .text("Approved")
                .removeClass("pfl-status-pending")
                .addClass("pfl-status-approved");
            } else if (action === "deny") {
              row.find(".pfl-status")
                .text("Denied")
                .removeClass("pfl-status-pending")
                .addClass("pfl-status-denied");
            }
          });
          alert("Bulk action applied successfully!");
          location.reload();
        } else {
          alert("Error: " + response.data);
        }
      },
      error: function () {
        alert("An error occurred. Please try again.");
      },
    });
  });


  // Handle user denial
  $(".pfl-deny-user").on("click", function (e) {
    e.preventDefault()

    if (!confirm("Are you sure you want to deny this user?")) {
      return
    }

    var userId = $(this).data("user-id")
    var button = $(this)

    $.ajax({
      url: pfl_admin_ajax.ajax_url,
      type: "POST",
      data: {
        action: "pfl_deny_user",
        user_id: userId,
        nonce: pfl_admin_ajax.nonce,
      },
      success: (response) => {
        if (response.success) {
          button.closest("tr").fadeOut()
          alert("User denied successfully!")
          location.reload();
        } else {
          alert("Error: " + response.data)
        }
      },
    })
  })

  $('.pfl-view-request').on('click', function(){
    let requestId = $(this).data('request-id');
    let $modalBody = $('#requestDetailsContent');

    $modalBody.html('<p class="text-gray-500">Loading...</p>');

    $.get(pfl_admin_ajax.ajax_url,{
      action: 'pfl_get_flight_requests_by_id',
      request_id: requestId
    }, function(response){
      console.log(response);
      if(response.success){
        let html = `
          <div id="request-fields">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
              <div class="relative pfl-form-group">
                <label for="request_airline" class="block text-sm font-medium text-gray-700 mb-1">Flight:</label>
                <input type="text" id="request_airline_name" name="request_airline_name" placeholder="Search airline..."
                 class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="${response.data.airline_code}${response.data.flight_number}" readOnly>
              </div>
              <div class="pfl-form-group">
                <label for="travel_date" class="block text-sm font-medium text-gray-700 mb-1">Date of Travel *</label>
                <input type="date" id="travel_date" name="travel_date" 
                 class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="${response.data.travel_date}" readOnly>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
              <div class="relative pfl-form-group">
                <label for="from_airport" class="block text-sm font-medium text-gray-700 mb-1">From *</label>
                <input type="text" id="from_airport" name="from_airport" placeholder="Search airport..."
                 class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="${response.data.from_airport_id}" readOnly>
              </div>
                      
              <div class="relative pfl-form-group">
                <label for="to_airport" class="block text-sm font-medium text-gray-700 mb-1">To *</label>
                <input type="text" id="to_airport" name="to_airport" placeholder="Search airport..." 
                 class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="${response.data.to_airport_id}" readOnly>
              </div>
            </div>          
                  
            <div class="pfl-form-group">
              <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea id="notes" name="notes" maxlength="300" placeholder="Additional information..." rows="4"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" readOnly>${response.data.notes}</textarea>
              <p class="mt-1 text-sm text-gray-500"><span class="pfl-char-count">0</span>/300 characters</p>
            </div>
          </div>
      `;
        $modalBody.html(html);
      } 
      else {
          $modalBody.html('<p class="text-red-500">Unable to load details.</p>');
        }
    });
  });

  $('.pfl-view-retired').on('click', function() {
    let userId = $(this).data('user-id');
    let $modalBody = $('#userDetailsContent');

    $modalBody.html('<p class="text-gray-500">Loading...</p>');

    $.get(pfl_admin_ajax.ajax_url, {
      action: 'pfl_get_user_profile',
      user_id: userId
    }, function(response) {
      console.log(response);
      if (response.success) {
        let profile = response.data;
        let status = profile.approval_status;

        if(status === 'approved'){
          $('#modal-approve').hide();
        } else{
          $('#modal-approve').show();
        }

        let html = `
                    <div class="flex justify-center">
                        ${profile.upload_id_url ? `<a href="${profile.upload_id_url}" target="_blank"><img class="rounded-lg shadow-md border border-gray-300 hover:shadow-lg transition" src="${profile.upload_id_url}"></a>` : ''}
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div><p class="text-sm font-medium text-gray-500">Full Name</p>
                        <p class="text-base font-semibold text-gray-900">${profile.display_name}</p>
                      </div>
                      <div><p class="text-sm font-medium text-gray-500">Airline Name</p>
                        <p class="text-base font-semibold text-gray-900">${profile.airline_name ?? 'N/A'} (${profile.domain ?? ''})</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="text-base font-semibold capitalize text-gray-900">${profile.status}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-500">Approval Status</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">${profile.approval_status}</span>
                      </div>

                      
                        <div>                          
                          <p class="text-sm font-medium text-gray-500">Phone Number</p>
                          <p class="text-base font-semibold text-gray-900">${profile.phone_number ?? ''}</p>
                        </div>
                        <div>
                          <p><p class="text-sm font-medium text-gray-500">${profile.status == 'retired' ? 'Retirement' : 'Employment'} Date</p>
                          <p class="text-base font-semibold text-gray-900">${profile.employment_retirement_date ?? ''}</p>
                        </div>
                        <div>
                          <p><p class="text-sm font-medium text-gray-500">Airline Job</p>
                          <p class="text-base font-semibold text-gray-900">${profile.airline_job ?? ''}</p>
                        </div>
                        <div>
                          <p class="text-sm font-medium text-gray-500">Years Worked</p>
                          <p class="text-base font-semibold text-gray-900">${profile.years_worked ?? ''}</p>
                        </div>
                      
                    </div>
                    
          `;

          $modalBody.html(html);
          $('#modal-approve, #modal-deny').attr('data-user-id', profile.user_id);
        } else {
          $modalBody.html('<p class="text-red-500">Unable to load details.</p>');
        }
      });
  });
})


document.addEventListener("DOMContentLoaded", function () {
  const table = document.getElementById("airlines-table");
  const rows = Array.from(table.querySelectorAll("tbody tr"));
  const searchInput = document.getElementById("table-search");
  const rowsPerPageSelect = document.getElementById("rows-per-page");
  const pagination = document.getElementById("pagination");

  let currentPage = 1;
  let rowsPerPage = parseInt(rowsPerPageSelect.value);
  let filteredRows = rows;

  function renderTable() {
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    // hide all rows
    rows.forEach((row) => (row.style.display = "none"));

    // show only filtered rows for this page
    filteredRows.slice(start, end).forEach((row) => (row.style.display = ""));
    renderPagination();
  }

  function renderPagination() {
    pagination.innerHTML = "";
    const pageCount = Math.ceil(filteredRows.length / rowsPerPage);

    // Previous button
    if (currentPage > 1) {
      const prev = document.createElement("button");
      prev.textContent = "Prev";
      prev.className = "px-3 py-1 border rounded hover:bg-gray-100";
      prev.onclick = () => {
        currentPage--;
        renderTable();
      };
      pagination.appendChild(prev);
    }

    // Page numbers
    for (let i = 1; i <= pageCount; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.className =
        "px-3 py-1 border rounded " +
        (i === currentPage
          ? "bg-blue-600 text-white"
          : "hover:bg-gray-100 text-gray-700");
      btn.onclick = () => {
        currentPage = i;
        renderTable();
      };
      pagination.appendChild(btn);
    }

    // Next button
    if (currentPage < pageCount) {
      const next = document.createElement("button");
      next.textContent = "Next";
      next.className = "px-3 py-1 border rounded hover:bg-gray-100";
      next.onclick = () => {
        currentPage++;
        renderTable();
      };
      pagination.appendChild(next);
    }
  }

  // Search filter
  searchInput.addEventListener("input", function () {
    const q = this.value.toLowerCase();
    filteredRows = rows.filter((row) =>
      row.innerText.toLowerCase().includes(q)
    );
    currentPage = 1;
    renderTable();
  });

  // Rows per page change
  rowsPerPageSelect.addEventListener("change", function () {
    rowsPerPage = parseInt(this.value);
    currentPage = 1;
    renderTable();
  });

  renderTable();
});

