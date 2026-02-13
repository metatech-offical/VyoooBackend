$(document).ready(function () {
    var domainUrl = $("#appUrl").val();
    if (!domainUrl || domainUrl === "http://yourdomain.com/") {
        domainUrl = window.location.origin + "/";
    }
    if (!domainUrl.endsWith("/")) domainUrl += "/";

    $("#loginForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData($("#loginForm")[0]);
        $.ajax({
            url: domainUrl + "loginForm",
            type: "POST",
            data: formData,
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
                console.log(response);
                if (response.status) {
                    window.location.href = domainUrl + "dashboard";
                } else {
                    $.NotificationApp.send(
                        "Oops",
                        response.message,
                        "top-right",
                        "rgba(0,0,0,0.2)",
                        "error",
                        3000
                    );
                }
            },
            error: function (xhr, status, error) {
                console.log(xhr, status, error);
                var msg = "Login request failed. Check console or try again.";
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if (xhr.status === 419) msg = "Session expired. Please refresh the page.";
                else if (xhr.status) msg = "Error " + xhr.status + ". Is the server running at " + domainUrl + "?";
                if (typeof $.NotificationApp !== "undefined") {
                    $.NotificationApp.send("Oops", msg, "top-right", "rgba(0,0,0,0.2)", "error", 5000);
                } else { alert(msg); }
            },
        });
    });

    $("#forgotPasswordForm").on("submit", function (event) {
        event.preventDefault();
        var formData = new FormData(this);

        var newPassword = $("#new_password").val();
        var confirmPassword = $("#confirm_password").val();

        if (newPassword !== confirmPassword) {
            showErrorToast("Passwords do not match!");
            return;
        }

        $.ajax({
            url: domainUrl + "forgotPasswordForm",
            type: "POST",
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.status) {
                    $("#forgotPasswordModal").modal("hide");
                    resetForm("#forgotPasswordForm");
                    resetForm("#loginForm");
                    showSuccessToast(response.message);
                } else {
                    showErrorToast(response.message);
                }
            },
            error: function (err) {
                console.log(err);
            },
        });
    });
});
