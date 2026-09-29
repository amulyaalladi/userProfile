$(document).ready(function () {

    // If already logged in, skip straight to profile
    if (localStorage.getItem('authToken')) {
        window.location.href = 'profile.html';
        return;
    }

    $('#loginBtn').on('click', function (e) {
        e.preventDefault(); // strictly no form submission

        hideAlert();

        const username = $('#loginIdentifier').val().trim();
        const password = $('#loginPassword').val();

        if (!username || !password) {
            showAlert('Please enter your username/email and password.', 'danger');
            return;
        }

        setLoading(true);

        $.ajax({
            url: API_BASE + 'php/login.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username, password }),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Session is maintained ONLY via browser localStorage
                    localStorage.setItem('authToken', response.token);
                    localStorage.setItem('authUser', JSON.stringify(response.user));

                    showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(function () {
                        window.location.href = 'profile.html';
                    }, 800);
                } else {
                    showAlert(response.message || 'Login failed.', 'danger');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Something went wrong. Please try again.';
                showAlert(msg, 'danger');
            },
            complete: function () {
                setLoading(false);
            }
        });
    });

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
        $('#loginBtn').prop('disabled', isLoading);
        $('#loginBtnText').text(isLoading ? 'Logging in...' : 'Login');
    }
});
