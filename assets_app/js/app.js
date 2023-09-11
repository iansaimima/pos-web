var rootUrl = window.location.protocol.concat("//").concat(window.location.host).concat("/sijualpos-web/");
function getUrlParams(dParam) {
    var dPageURL = window.location.search.substring(1),
        dURLVariables = dPageURL.split('&'),
        dParameterName,
        i;

    for (i = 0; i < dURLVariables.length; i++) {
        dParameterName = dURLVariables[i].split('=');

        if (dParameterName[0] === dParam) {
            return dParameterName[1] === undefined ? true : decodeURIComponent(dParameterName[1]);
        }
    }
}

(function ($) {
    "use strict"

    var direction = getUrlParams('dir');
    if (direction != 'rtl') {
        direction = 'ltr';
    }

    new dezSettings({
        typography: "roboto",
        version: "light",
        layout: "vertical",
        headerBg: "color_1",
        navheaderBg: "color_3",
        sidebarBg: "color_1",
        sidebarStyle: "full",
        sidebarPosition: "fixed",
        headerPosition: "fixed",
        containerLayout: "wide",
        direction: direction
    });

})(jQuery);

var status_code = {
    200: function (json) {
        try {
            $('input[name=' + json.csrf_token_name + ']').val(json.csrf_hash);
        } catch (e) {
            regenerateCsrfToken();
        }
    },
    201: function (json) {
        try {
            $('input[name=' + json.csrf_token_name + ']').val(json.csrf_hash);
        } catch (e) {
            regenerateCsrfToken();
        }
    },
    400: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    },
    401: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }

    },
    402: function (xhr) {
        try {
            var json = JSON.parse(xhr.responseText);
            $("#langganan-berakhir-pesan").html(json.message.replace(/\n/, "<br/>"));
            $("#langganan-berakhir").show();
            regenerateCsrfToken();
        } catch (error) {
            window.location.href = 'https://langganan.gutsypos.com/';
        }
    },
    403: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    },
    404: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    },
    405: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    },
    406: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    },
    500: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    },
    501: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    },
    502: function (xhr) {
        regenerateCsrfToken();

        try {
            var json = JSON.parse(xhr.responseText);
            show_toast(xhr.statusText, json.message, "error");
        } catch (error) {
            show_toast("Error", "Application Response Error", "error");
        }
    }
};

function ajax_get_loader_text(url, data, target, text, resp_) {
    $.ajax({
        url: url,
        type: "GET",
        data: data,
        beforeSend: function (xhr) {
            $(target).html(text);
        },
        complete: function (jqXHR, textStatus) { },
        statusCode: status_code,
        success: function (resp, status) {
            resp_(resp);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $("#loader").hide();
        }
    });
}

function ajax_get(url, data, resp_) {
    $.ajax({
        url: url,
        type: "GET",
        data: data,
        beforeSend: function (xhr) {
            $("#loader").show();
        },
        complete: function (jqXHR, textStatus) {
            $("#loader").hide();
        },
        statusCode: status_code,
        success: function (resp, status) {
            resp_(resp);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $("#loader").hide();
        }
    });
}

function ajax_post(url, data, resp_) {
    var ajax = $.ajax({
        url: url,
        type: "POST",
        data: data,
        beforeSend: function (xhr) {
            $("#loader").show();
        },
        complete: function (jqXHR, textStatus) {
            $("#loader").hide();
        },
        statusCode: status_code,
        success: function (resp, status) {
            resp_(resp);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $("#loader").hide();
        }
    });
}

function ajax_post_file(url, data, resp_) {
    var ajax = $.ajax({
        url: url,
        type: "POST",
        enctype: 'multipart/form-data',
        data: data,
        processData: false, // Important!
        contentType: false,
        cache: false,
        timeout: 600000,
        beforeSend: function (xhr) {
            $("#loader").show();
        },
        complete: function (jqXHR, textStatus) {
            $("#loader").hide();
        },
        statusCode: status_code,
        success: function (resp, status) {
            resp_(resp);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $("#loader").hide();
        }
    });
}

function show_toast(title, message, type, closeButton) {

    var timeOut = "5000";
    var extendedTimeOut = "1000";

    if (closeButton == undefined) closeButton = false;
    if (closeButton == true) {
        timeOut = "0";
        extendedTimeOut = "0";
    }
    toastr.options = {
        "closeButton": closeButton,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-center",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": timeOut,
        "extendedTimeOut": extendedTimeOut,
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }
    toastr[type](message, title);
}



function copy_to_clipboard(val) {
    var ClipboardHelper = {

        copyElement: function ($element) {
            this.copyText($element.text())
        },
        copyText: function (text) // Linebreaks with \n
        {
            var $tempInput = $("<textarea>");
            $("body").append($tempInput);
            $tempInput.val(text).select();
            document.execCommand("copy");
            $tempInput.remove();
            show_toast("Success", "Copy success", "success");
        }
    };

    ClipboardHelper.copyText(val);
}

function toCurrency(value) {
    // Create USD currency formatter.
    var formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'IDR',
    });

    var formatted = formatter.format(value);
    formatted = formatted.replace("IDR", "");
    // formatted = formatted.replace(",", ".");
    // formatted = formatted.replace(",", ".");
    // formatted = formatted.replace(".00", "");

    return formatted;
}

function toNumber(string) {
    string = string + "";
    var number = string.replace(/[^,\d]/g, "").toString(),
        split = number.split(","),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    return number;
}

/* Fungsi formatRupiah */
function formatCurrency(angka, prefix) {
    angka = angka + "";
    var number_string = angka.replace(/[^,\d]/g, "").toString(),
        split = number_string.split(","),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if (ribuan) {
        separator = sisa ? "." : "";
        rupiah += separator + ribuan.join(".");
    }

    rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
    return prefix == undefined ? rupiah : rupiah ? "Rp. " + rupiah : "";
}