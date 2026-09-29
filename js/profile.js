$(document).ready(function(){
    const token= localStorage.getItem('authToken');

    if(!token){
        window.location.href='login.html';
        return;
    }

    loadProfile();$(document).ready(function () {

    const token = localStorage.getItem('authToken');

    // Guard: must be logged in (token lives only in localStorage)
    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    loadProfile();

    function loadProfile() {
        hideAlert();

        $.ajax({
            url: API_BASE + 'php/profile.php',
            type: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#profileUsername').text(response.account.username);
                    $('#profileEmail').text(response.account.email);

                    $('#age').val(response.profile.age ?? '');
                    $('#dob').val(response.profile.dob ?? '');
                    $('#contact').val(response.profile.contact ?? '');
                    $('#address').val(response.profile.address ?? '');
                } else {
                    handleAuthFailure(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    handleAuthFailure('Session expired. Please log in again.');
                } else {
                    showAlert('Could not load your profile.', 'danger');
                }
            }
        });
    }

    $('#profileForm').on('submit', function (e) {
        e.preventDefault(); // no native form submission, just prevented here
    });

    $('#saveProfileBtn').on('click', function (e) {
        e.preventDefault(); // strictly no form submission

        hideAlert();

        const payload = {
            age: $('#age').val().trim(),
            dob: $('#dob').val().trim(),
            contact: $('#contact').val().trim(),
            address: $('#address').val().trim()
        };

        setLoading(true);

        $.ajax({
            url: API_BASE + 'php/profile.php',
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert('Profile updated successfully!', 'success');
                } else {
                    showAlert(response.message || 'Update failed.', 'danger');
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    handleAuthFailure('Session expired. Please log in again.');
                } else {
                    showAlert('Update failed. Please try again.', 'danger');
                }
            },
            complete: function () {
                setLoading(false);
            }
        });
    });

    $('#logoutBtn').on('click', function (e) {
        e.preventDefault();

        $.ajax({
            url: API_BASE + 'php/logout.php',
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            complete: function () {
                localStorage.removeItem('authToken');
                localStorage.removeItem('authUser');
                window.location.href = 'login.html';
            }
        });
    });

    function handleAuthFailure(message) {
        localStorage.removeItem('authToken');
        localStorage.removeItem('authUser');
        showAlert(message || 'Please log in again.', 'danger');
        setTimeout(function () {
            window.location.href = 'login.html';
        }, 1200);
    }

    function showAlert(message, type) {
        $('#alertBox')
            .removeClass('alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }

    function hideAlert() {
        $('#alertBox').hide();
    }

    function setLoading(isLoading) {
        $('#saveProfileBtn').prop('disabled', isLoading);
        $('#saveProfileBtnText').text(isLoading ? 'Saving...' : 'Save Changes');
    }
});


    function loadProfile(){
        hideAlert();

        $.ajax({
            url: API_BASE + 'php/profile.php',
            type: 'GET',
            headers: { 'Authorization': 'Bearer' + token},
            dataType: 'json',
            success: function (response){
                if(response.success){
                    $('#profileUsername').text(response.account.username);
                    $('#profileemail').text(response.account.email);

                    $('#age').val(response.profile.age ?? '');
                    $('#dob').val(response.profile.dob ?? '');
                    $('#contact').val(response.profile.contact ?? '');
                    $('#address').val(response.profile.address ?? '');
                } else{
                    handleAuthFailure(response.message);
                }
            },
            error: function (xhr){
                if(xhr.status ===401){
                    handleAuthFailure('Session expired. Please login again');
                } else {
                    showAlert('Could not load your profile.', 'danger');
                }
            }
        });
    }

    $('#profileForm').on('submit', function (e){
        e.preventDefault();
    });

    $('#saveProfileBtn').on('click', function (e) {
        e.preventDefault();

        hideAlert();

        const payload={
            age: $('#age').val().trim(),
            dob: $('#dob').val().trim(),
            contact: $('#contact').val().trim(),
            address: $('#address').val().trim()
        };

        setLoading(true);

$.ajax({
            url: API_BASE + 'php/profile.php',
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert('Profile updated successfully!', 'success');
                } else {
                    showAlert(response.message || 'Update failed.', 'danger');
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    handleAuthFailure('Session expired. Please log in again.');
                } else {
                    showAlert('Update failed. Please try again.', 'danger');
                }
            },
            complete: function () {
                setLoading(false);
            }
        });
    });

    $('#logoutBtn').on('click', function (e) {
        e.preventDefault();

        $.ajax({
            url: API_BASE + 'php/logout.php',
            type: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            complete: function () {
                localStorage.removeItem('authToken');
                localStorage.removeItem('authUser');
                window.location.href = 'login.html';
            }
        });
    });

    function handleAuthFailure(message) {
        localStorage.removeItem('authToken');
        localStorage.removeItem('authUser');
        showAlert(message || 'Please log in again.', 'danger');
        setTimeout(function () {
            window.location.href = 'login.html';
        }, 1200);
    }

    function showAlert(message, type) {
        $('#alertBox')
            .removeClass('alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }

    function hideAlert() {
        $('#alertBox').hide();
    }

    function setLoading(isLoading) {
        $('#saveProfileBtn').prop('disabled', isLoading);
        $('#saveProfileBtnText').text(isLoading ? 'Saving...' : 'Save Changes');
    }
});
