function callAppInstallation() {
    $.ajax({
        url: '',

        success: function (data) {

            if (data != false) {
                calling = true;
                $('div.box-body').append(data);

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {'type': 'launch', _csrf: yii.getCsrfToken()},
                    success: function (data) {
                        $('div#waiting').remove();

                        if (data.error == 1) {
                            alert(data.error_message);
                        }
                        else {
                            callAppInstallation();
                        }
                    }
                });
            }
            else {
                // Reload the current page, without using the cache
                document.location.reload(true);
            }
        }
    });
}


callAppInstallation();