$(function () {
    // Multiple images preview in browser
    let imagesPreview = function (input, placeToInsertImagePreview) {

        if (input.files) {
            let filesAmount = input.files.length;

            // get files from input
            let files = $('#trick_form_images').prop("files");
            // get files' name and put them into an array
            let names = $.map(files, function (val) {
                return val.name;
            });

            for (let i = 0; i < filesAmount; i++) {
                let reader = new FileReader();

                reader.onload = function (event) {
                    // add img foreach input element
                    let img = $($.parseHTML('<img>')).attr({
                        class: 'image-thumbnail',
                        id: 'image-' + i,
                        src: event.target.result
                    }).appendTo(placeToInsertImagePreview);
                    let imageOptions = $($.parseHTML('<div>')).attr({
                        class: 'image-gallery-options',
                        id: 'option-' + i
                    });
                    let radioButton = $($.parseHTML('<input>'));
                    radioButton.attr({
                        class: 'form-check-input',
                        type: 'radio',
                        name: 'mainPictureRadio',
                        id: 'mainPictureRadio' + i,
                        value: i
                    });
                    imageOptions.append(radioButton);
                    img.after(imageOptions);

                }
                reader.readAsDataURL(input.files[i]);
            }
        }

    };

    $('.images-input').on('change', function () {
        imagesPreview(this, '.images-gallery');
    });
});