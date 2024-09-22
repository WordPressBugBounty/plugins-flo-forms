function displayCaptchaOptions() {
	var captchaRow = jQuery(".captcha-row");
	var captchaChecked = jQuery(".captcha-true").prop("checked");
	if (typeof captchaChecked != "undefined" && captchaChecked) {
		captchaRow.show();
	} else {
		captchaRow.hide();
	}
}

function displayReminderOptions() {
	var emailReminderRow = jQuery(".email-reminder-option-row");
	var emailReminderChecked = jQuery(".email-reminder-true").attr("checked");

	if (
		typeof emailReminderChecked != "undefined" &&
		emailReminderChecked == "checked"
	) {
		emailReminderRow.show();
	} else {
		emailReminderRow.hide();
	}
}

function floIsFirefox() {
	if (navigator.userAgent.search("Firefox") > -1) {
		return true;
	} else {
		return false;
	}
}

function initSortable() {
	var field_groups = document.querySelectorAll(".form-group-section");
	var sort_handle;

	console.log("initSortable entered... ");

	if (floIsFirefox()) {
		sort_handle = ".form-group";
	} else {
		sort_handle = ".sort-handle";
	}

	field_groups.forEach(function (field_group, field_group_index) {
		Sortable.create(field_group, {
			group: "form_fields",
			handle: sort_handle,
			//delay: 10,

			// Element dragging started
			onStart: function (/**Event*/ evt) {
				jQuery(".form-group").removeClass("focused");
			},

			// Element dragging ended
			onEnd: function (/**Event*/ evt) {
				var itemEl = evt.item; // dragged HTMLElement
				var field_ids_reordered = [[], []];

				jQuery(".form-group-section").each(function (group_index) {
					jQuery(this)
						.find(".form-group")
						.each(function (group_field_index) {
							//console.log(group_index);
							var current_classes = this.classList;

							jQuery.each(current_classes, function (index, class_name) {
								if (class_name.includes("fid_")) {
									field_ids_reordered[group_index].push(
										class_name.replace("fid_", "")
									);
								}
							});
						});

					window.flo_form_fields = field_ids_reordered; // we store here the columns fields IDs to be able to access it from VUEjs

					// for now we just store the fields IDs order, and then on submit we will
					// dispatch the even so the real schema gets updated
					jQuery(".schema-fields-ids").val(field_ids_reordered.join());

					// document.querySelector('.schema-fields-ids').dispatchEvent(new Event('input'));
				});
			},
		}); // That's all.
	});
}

(function ($) {
	"use strict";

	$(document).ready(function () {
		initSortable();

		$(function () {
			// init froala for the enhanced message editor
			if ($(".text-confirmation-value").length) {
				$(".text-confirmation-value").froalaEditor({
					key: "RG4H4B12B10iB6E5C3A4I2I3C9B6C5E5C-11NGNe1IODMGYNSFKV==",
					charCounterCount: false,
					editorClass: "froala-text-confirmation-value",
					//toolbarButtons: ['bold', 'italic', 'underline', 'color', 'paragraphFormat', 'insertLink', 'align', 'undo', 'redo']
					toolbarButtons: [
						"bold",
						"italic",
						"underline",
						"paragraphFormat",
						"insertLink",
						"align",
						"undo",
						"redo",
					],
				});
			}
		});

		//displayCaptchaOptions();

		$(function () {
			if ($(".form-tabs").length) {
				$(".form-tabs").tabs({
					active: 0,
				});
			}

			if ($(".ff-pro .preview-tabs").length) {
				$(".ff-pro .preview-tabs").tabs({
					active: 0,
				});
			}

			if ($(".ff-pro.start-how-to-tabs").length) {
				$(".ff-pro.start-how-to-tabs").tabs({
					active: 0,
				});
			}
		});

		$("li.add-field, li.form-settings").click(function () {
			// when the 1st and 3rd tabs are clicked we want to remove the focus from any
			// currently selected fields
			$(".form-preview .form-group").removeClass("focused");
		});

		$("li.fields-settings").click(function () {
			// when the field settings tab is clicked we trigger a click
			// on the first available field to make sure we show the settings for the 1st field
			$(".form-preview .form-group-one .form-group:first-child").click();
		});

		$(".form-settings-outer-wrapper").on(
			"click",
			".form-preview .form-group",
			function () {
				// get the classes for the .form-group
				// we should have something like this: 'form-group valid width-100 fid_6658166 field_nr_1 field-input'
				var field_class = $(this).attr("class");

				// among all the classes we will select the one wich starts with 'fid_'
				var field_id = field_class.match(/(fid_)\w+/g);

				$(".form-preview .form-group").removeClass("focused");
				$(this).addClass("focused");

				$(".fields-controls .field-settings").removeClass("visible"); // remove the visibale classfrom all the field blocks
				$(".fields-controls [data-field_id=" + field_id + "]").addClass(
					"visible"
				); // make only the selecte field's options visible
				$("#form-tabs").tabs("option", "active", 1);
			}
		);

		$(".fields-controls").on("change", ".choice-layout", function () {
			var field_id = $(this).parents(".field-settings").data("field_id"),
				preview_row = $(".form-preview li." + field_id);
			preview_row
				.removeClass("one-column two-columns three-columns side-by-side")
				.addClass($(this).val());
		});

		// Save form click
		$("#flo-publishing-action").on("click", "#flo_publish", function () {
			// in the '.schema-fields-ids' we store the fields order, and we need to dispatch the event in order to trigger the schema reordering
			document
				.querySelector(".schema-fields-ids")
				.dispatchEvent(new Event("input"));

			$("#flo-publishing-action .spinner").css("visibility", "visible");

			setTimeout(function () {
				// just wait a bit to make sure the schema order has completed
				$("#submitdiv #publish").click();
			}, 150);
		});

		jQuery(".flo-suggest-page").each(function () {
			var self = this;

			$(self).autocomplete({
				source: ajaxurl + "?action=search_page",
				minLength: 2,
				select: function (event, ui) {
					$(".confimation-page-id").val(ui.item.id); // write the page ID to the hidden input
				},
			});
		});

		// init copy to clipboard on click
		jQuery(".shortcode-input--copy").click(function () {
			copyToClipboard(jQuery(".shortcode-input").val());
			jQuery(".shortcode-input--copy-msg").show("slow");
		});

		// coppy to clipboard
		const copyToClipboard = (str) => {
			const el = document.createElement("textarea");
			el.value = str;
			document.body.appendChild(el);
			el.select();
			document.execCommand("copy");
			document.body.removeChild(el);
		};
	});

	$(".form-template-save").on("click", function (event) {
		event.preventDefault();

		$("li.form-design a").click(); // make sure the form design tab is visible

		$(".save-template-spinner").css("visibility", "visible");
		$(".form-template-messages-container").html(""); // clear all previous error messages

		setTimeout(() => {
			html2canvas($(".vue-form-generator")[0]).then(function (canvas) {
				//$('.form-template-img').attr('src', encodeURI(canvas.toDataURL('image/jpeg', 0.9)) );

				var img_src = encodeURI(canvas.toDataURL("image/jpeg", 0.9));

				//console.log(img_src);

				if ($(".form-template-name").val().length == 0) {
					$(".form-template-messages-container").append(
						" <p>Add please a Title for the template.</p> "
					);
					$(".save-template-spinner").css("visibility", "hidden");
				} else {

					jQuery.ajax({
						url: ajaxurl,
						//data: '&action=save_form_template&schema='+$('.flo-form-schema').serialize()+'&template_title='+$('.form-template-name').val()+'&img_src='+jQuery('.form-template-image-src').val(),
						data:
							"&action=save_form_template&nonce=" + flo_ajax_var.nonce + "&schema=" +
							$(".flo-form-schema").serialize() +
							"&template_title=" +
							$(".form-template-name").val() +
							"&img_src=" +
							img_src,
						type: "POST",
						dataType: "json",
						cache: false,
						success: function (json) {
							$(".save-template-spinner").css("visibility", "hidden");
							$(".form-template-messages-container").append(
								"<br/><p style='color: green;'>" + json.message + "</p>"
							);
						},
					});
				}
			});
		}, 20);
	});
})(jQuery);

/**
 *
 * Mark an entry as read or unread
 *
 */
function entryReadUnread(obj) {
	var is_read = obj.data("entry_read"),
		post_id = obj.data("post_id");

	jQuery.ajax({
		url: ajaxurl,
		data:
			"&action=entry_read_unread&is_read=" + is_read + "&post_id=" + post_id + "&nonce=" + flo_ajax_var.nonce,
		type: "POST",
		dataType: "json",
		cache: false,
		success: function (json) {
			obj.data("post_id", json.is_read); // update the is_read data attribute
			obj.text(json.text); // update the button text
			obj.parents("tr").toggleClass("entry-read");
		},
	});
}

function export_forms_options() {
	//event.preventDefault();

	jQuery.ajax({
		url: ajaxurl,
		data: "&action=flo_export_forms_options",
		type: "POST",
		dataType: "json",
		//      cache: false,
		success: function (json) {
			console.log("json: ", json);
			var dataStr =
				"data:text/json;charset=utf-8," +
				encodeURIComponent(JSON.stringify(json, undefined, 2));
			console.log("dataSt: ", dataStr);
			var dlAnchorElem = document.getElementById(
				"downloadFloFormsOtionsAnchorElem"
			);
			dlAnchorElem.setAttribute("href", dataStr);
			dlAnchorElem.setAttribute("download", "flo-forms-options.json");
			dlAnchorElem.click();
		},
	});
}

function import_forms_options() {
	var formData = new FormData();
	formData.append("action", "flo_import_forms_options");
	formData.append("nonce", flo_ajax_var.nonce);

	// Attach file
	formData.append(
		"options_file",
		jQuery(".flo-forms-import-settings-file")[0].files[0]
	);

	jQuery(".spinner-import").css("visibility", "visible");
	jQuery(".import-msg").html("");

	jQuery.ajax({
		url: ajaxurl,
		//data: '&action=flo_import_forms_options',
		data: formData,
		type: "POST",
		contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
		processData: false, // NEEDED, DON'T OMIT THIS
		dataType: "json",
		//      cache: false,
		success: function (json) {
			console.log("json: ", json);
			jQuery(".spinner-import").css("visibility", "hidden");
			jQuery(".import-msg").html(json.msg);

			if (json.status == "success") {
				setTimeout(function () {
					location.reload();
				}, 1300);
			}
		},
	});
}

function formSentTestEmail() {
	jQuery(".no-email-msg").hide();
	var email = jQuery("#send-test-email").val();
	var nonce = jQuery("#flo_form_nonce").val();

	jQuery(".spinner-send-test-email").css("visibility", "visible");

	jQuery.ajax({
		url: ajaxurl,
		data: "&action=flo_send_test_email&email=" + email + "&nonce=" + nonce,
		type: "POST",
		dataType: "json",
		//      cache: false,
		success: function (json) {
			jQuery(".spinner-send-test-email").css("visibility", "hidden");

			if (json.error) {
				jQuery(".test-email-response-container").html(json.error_message);
			} else {
				jQuery(".test-email-response-container").html(json.success_message);
			}
		},
	});
}

/*Close premium features popup box*/
function ffClosePrfb() {
	jQuery(".ff-premium-feat-box--popup-overlay").removeClass("popup-opened");
}

/*Open premium features popup box*/
function ffOpenPrfb() {
	jQuery(".ff-premium-feat-box--popup-overlay").addClass("popup-opened");
}
