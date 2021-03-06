jQuery(function($) {
    // Add new gallery thumbnail uploader
    $('body').on( 'click', '.hkh-upload-button', function(e) {
        e.preventDefault();

        let button = $(this), custom_uploader = wp.media({
            title: 'Insert image',
            library : {
                type : 'image'
            },
            button: {
                text: 'Select thumbnail'
            },
            multiple: false
        }).on('select', function() {
            let attachment = custom_uploader.state().get('selection').first().toJSON();

            button.find(".hkh-image-upload-container")
                .css("background-image", "url('" + attachment.url + "')")
            ;

            button.attr("data-image-id", attachment.id);
            button.find(".hkh-placeholder-text").text("Change thumbnail");

            $("input[name=thumbnail_id]").val(attachment.id);
        }).open();
    });

    // Replace gallery image uploader
    $("body").on("click", ".hkh-replace-image", function(e) {
        e.preventDefault();

        let button = $(this), custom_uploader = wp.media({
            title: 'Replace image',
            library : {
                type : 'image'
            },
            button: {
                text: 'Replace image'
            },
            multiple: false
        }).on('select', function() {
            let attachment = custom_uploader.state().get('selection').first().toJSON();

            button.find(".hkh-image-container").css("background-image", "url('" + attachment.url + "')");
            button.closest(".hkh-grid-row").find(".hkh-gallery-image-input").val(attachment.id);
        }).open();
    });

    let images_container = $(".hkh-image-info-grid");

    // Add new image
    $("body").on("click", ".hkh-gallery-new-image", function(e) {
        e.preventDefault();

        let button = $(this), custom_uploader = wp.media({
            title: 'Add image(s)',
            library : {
                type : 'image'
            },
            button: {
                text: 'Add selected image(s)'
            },
            multiple: true
        }).on("select", function() {
            let attachments = custom_uploader.state().get("selection").toJSON();

            // Add each attachment as a new row
            attachments.forEach(function(attachment) {
                let thumbnail_url = attachment.sizes["thumbnail"].url;

                images_container.append(`
                    <div class="hkh-grid-row">
                        <input type="hidden" name="hkh_images[new_${attachment.id}][id]" value="0" />
                        
                        <div class="hkh-col">
                            <div class="hkh-grid-item hkh-replace-image">
                                <div class="hkh-image-container" style="background-image: url('${thumbnail_url}')">
                                    <div class="hkh-image-hover">
                                        <p>Click to Replace</p>
                                    </div>
                                </div>
                            </div>
    
                            <div class="button hkh-remove-image">Remove</div>
                        </div>
    
                        <input class="hkh-gallery-image-input" type="hidden" name="hkh_images[new_${attachment.id}][image_id]" value="${attachment.id}" />
    
                        <div class="hkh-grid-desc">
                            <div class="hkh-desc-title">
                                <p>Title</p>
                                <input type="text" name="hkh_images[new_${attachment.id}][title]" placeholder="Title" value="${attachment.caption}" />
                            </div>
    
                            <div class="hkh-desc-text">
                                <p>Description</p>
                                <textarea name="hkh_images[new_${attachment.id}][description]" placeholder="Description" rows="3">${attachment.description}</textarea>
                            </div>
                        </div>
                    </div>
    
                    <hr style="width: 100%;" />`)
                });
        }).open();
    });

    // Remove image
    $("body").on("click", ".hkh-remove-image", function(e) {
        e.preventDefault();

        $(this).closest(".hkh-grid-row").remove();
    });

    x = {
        "id": 195,
        "title": "fullsizeoutput-333e",
        "filename": "fullsizeoutput-333e.jpg",
        "url": "http://mglouth.local/wp-content/uploads/2022/01/fullsizeoutput-333e.jpg",
        "link": "http://mglouth.local/gallery/fullsizeoutput-333e/",
        "alt": "",
        "author": "1",
        "description": "",
        "caption": "",
        "name": "fullsizeoutput-333e",
        "status": "inherit",
        "uploadedTo": 153,
        "date": "2022-01-23T15:35:54.000Z",
        "modified": "2022-01-23T15:35:54.000Z",
        "menuOrder": 0,
        "mime": "image/jpeg",
        "type": "image",
        "subtype": "jpeg",
        "icon": "http://mglouth.local/wp-includes/images/media/default.png",
        "dateFormatted": "January 23, 2022",
        "nonces": {
            "update": "5a1ac565b0",
            "delete": "0894dc1209",
            "edit": "2bfefe633b"
        },
        "editLink": "http://mglouth.local/wp-admin/post.php?post=195&action=edit",
        "meta": false,
        "authorName": "harry",
        "authorLink": "http://mglouth.local/wp-admin/profile.php",
        "uploadedToTitle": "Gallery",
        "uploadedToLink": "http://mglouth.local/wp-admin/post.php?post=153&action=edit",
        "filesizeInBytes": 205523,
        "filesizeHumanReadable": "201 KB",
        "context": "",
        "height": 600,
        "width": 800,
        "orientation": "landscape",
        "sizes": {
            "thumbnail": {
                "height": 150,
                "width": 150,
                "url": "http://mglouth.local/wp-content/uploads/2022/01/fullsizeoutput-333e-150x150.jpg",
                "orientation": "landscape"
            },
            "medium": {
                "height": 225,
                "width": 300,
                "url": "http://mglouth.local/wp-content/uploads/2022/01/fullsizeoutput-333e-300x225.jpg",
                "orientation": "landscape"
            },
            "full": {
                "url": "http://mglouth.local/wp-content/uploads/2022/01/fullsizeoutput-333e.jpg",
                "height": 600,
                "width": 800,
                "orientation": "landscape"
            }
        },
        "compat": {
            "item": "",
            "meta": ""
        },
        "acf_errors": false
    }

    // on remove button click
    /*$('body').on('click', '.misha-rmv', function(e){

        e.preventDefault();

        var button = $(this);
        button.next().val(''); // emptying the hidden field
        button.hide().prev().html('Upload image');
    });*/
})