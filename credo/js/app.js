//JQuery start
class API_Helper {
    API = "/credo/API/index.php";

    /**
     * მომხმარებლის ავტორიზაცია სერვერის მხარეს
     * @param formObject
     */
    login(formObject) {
        let checkUsername = this.checkEmptyString(formObject[0]['user']);
        let checkPassword = this.checkEmptyString(formObject[0]['password']);
        if (checkUsername === true || checkPassword === true) {
            alert('გთხოვთ შეავსოთ ავტორიზაციის ველები')
        } else {
            let userObject = {
                apiMethod: "Login",
                apiController: "Customer",
                parameters: {
                    user: formObject[0]['user'],
                    password: formObject[0]['password']
                }
            }
            $.ajax({
                type: "POST",
                url: "/credo/API/index.php",
                data: JSON.stringify(userObject),
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                success: function (resp) {
                    if (resp.status === "success") {
                        alert(resp.comment);
                        window.location.replace("dashboard.php");
                    } else {
                        alert(resp.comment);
                        console.log(123);
                    }
                },
                cache: false
            });
        }
    }

    /**
     * მომხმარებლის რეგისტრაცია სერვერის მხარეს
     * @param formObject
     */
    register(formObject) {
        let checkUsername = this.checkEmptyString(formObject[0]['user']);
        let checkPassword_1 = this.checkEmptyString(formObject[0]['pwd_1']);
        let checkPassword_2 = this.checkEmptyString(formObject[0]['pwd_2']);

        if (checkPassword_1 === true || checkPassword_2 === true || checkUsername === true) {
            alert('გთხოვთ შეავსოთ სავალდებულო ველები')
            return false;
        }
        if (formObject[0]['pwd_1'] !== formObject[0]['pwd_2']) {
            alert("შეყვანილი პაროლები არ ემთხვევა")
            return false;
        }


        let userObject = {
            apiMethod: "Register",
            apiController: "Customer",
            parameters: {
                fulname: formObject[0]['fullname'],
                user: formObject[0]['username'],
                password: formObject[0]['pwd_1'],
                img: formObject[0]['img']
            }
        }

        $.ajax({
            type: "POST",
            url: "/credo/API/index.php",
            data: JSON.stringify(userObject),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (resp) {
                console.log(resp);
                if (resp.status === "success") {
                    alert(resp.comment);
                } else {
                    alert(resp.comment);
                    console.log(123);
                }
            },
            cache: false
        });

    }

    /**
     * მომხმარებლის პროფილის რედაქტირება სერვერის მხარეს
     * @param formObject
     */
    updateProfile(formObject) {

        let checkFullname = this.checkEmptyString(formObject[0]['fullname']);
        let checkImg = this.checkEmptyString(formObject[0]['img']);

        if (checkFullname === true && checkImg === true) {
            alert("გთხოვთ შეავსოთ ველი/_ები, რომელთაც ვარედაქტირებთ")
            return false;
        }

        let userObject = {
            apiMethod: "UpdateProfile",
            apiController: "Customer",
            parameters: {
                fullname: formObject[0]['fullname'],
                img: formObject[0]['img']
            }
        }

        $.ajax({
            type: "POST",
            url: "/credo/API/index.php",
            data: JSON.stringify(userObject),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (resp) {
                // console.log(resp);
                if (resp.status === "success") {
                    alert(resp.comment);
                    location.reload();
                } else {
                    if (resp.data === "UserSessionExpired") {
                        window.location.replace("index.html");
                        return false;
                    }
                    alert(resp.comment);
                    console.log(123);
                }
            },
            cache: false
        });

    }


    /**
     * მოწმდება str არის თუ არა შევსებული
     * @param str
     * @returns {boolean}
     */
    checkEmptyString(str) {
        switch (str) {
            case "":
            case 0:
            case "0":
            case null:
            case false:
            case typeof (str) == "undefined":
                return true;
            default:
                return false;
        }
    }

    /**
     * მოწმდება str არის თუ არა სასურველი გაფართოების ფაილი
     * @param str
     * @returns {boolean}
     */
    checkImgExtensions(str) {
        switch (str) {
            case "jpg":
            case "png":
            case "jpeg":
            case "tiff ":
                return true;
            default:
                return false;
        }

    }

}

const helper = new API_Helper();

$("#loginBtn").click(function () {
    let formObject = [
        {
            user: document.getElementById("usr").value,
            password: document.getElementById("pwd").value,
        }

    ]
    helper.login(formObject);

});
$("#registerBtn").click(function () {
    let formObject = [
        {
            fullname: document.getElementById("fullname").value,
            username: document.getElementById("user").value,
            pwd_1: document.getElementById("pwd_1").value,
            pwd_2: document.getElementById("pwd_2").value,
            img: document.getElementById("base64img").value,
        }

    ]
    helper.register(formObject);

});
$("#editProfileBtn").click(function () {
    let formObject = [
        {
            fullname: document.getElementById("fullname").value,
            img: document.getElementById("base64img").value,
        }


    ]
    helper.updateProfile(formObject);

});

function convertToBase64(element) {

    let selectedFile = element.files[0];
    let filetype = selectedFile.type;
    let reader = new FileReader();
    filetype = filetype.split("/");
    if (filetype[0] === "image") {
        let checkFileType = filetype[1].toLowerCase();
        var imageCheck = helper.checkImgExtensions(checkFileType);
    } else {
        alert("არასწორი ფაილის ფორმატი");
        return false;
    }

    if (imageCheck !== true) {
        alert("არასწორი ფაილის გაფართოება");
        return false;
    }

    reader.onloadend = function () {
        document.getElementById('base64img').value = reader.result;
    }
    reader.readAsDataURL(selectedFile);
}






