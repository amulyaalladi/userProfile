$(document).ready(function () {

    // If already logged in, skip straight to profile
    if (localStorage.getItem('authToken')) {
        window.location.href = 'profile.html';
        return;
    }

    $('#registerBtn').on('click', function (e) {
        e.preventDefault(); // strictly no form submission

        hideAlert();

        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();

        if (!username || !email || !password || !confirmPassword) {
            showAlert('Please fill in all fields.', 'danger');
            return;
        }

        if (password !== confirmPassword) {
            showAlert('Passwords do not match.', 'danger');
            return;
        }

        setLoading(true);

        $.ajax({
            url: API_BASE + 'php/register.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username, email, password }),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showAlert(response.message + ' Redirecting to login...', 'success');
                    setTimeout(function () {
                        window.location.href = 'login.html';
                    }, 1200);
                } else {
                    showAlert(response.message || 'Registration failed.', 'danger');
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
        $('#registerBtn').prop('disabled', isLoading);
        $('#registerBtnText').text(isLoading ? 'Registering...' : 'Register');
    }
});
