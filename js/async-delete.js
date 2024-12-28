jQuery(document).ready(function ($) {
    // Initialize the Datepicker
    $("#diu-cutoff-date").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        yearRange: "1990:2100"
    });

    const form = $("#diu-delete-users-form");
    const progressBar = $("#diu-progress-bar");
    const progressContainer = $("#diu-progress");
    const statusMessage = $("#diu-status-message");

    $("#diu-start-delete").on("click", function () {
        const userRole = $("#diu-user-role").val();
        const cutoffDate = $("#diu-cutoff-date").val();

        if (!userRole || !cutoffDate) {
            statusMessage.text("Please select a user role and enter a cutoff date.").addClass("text-red-500");
            return;
        }

        statusMessage.text("Preparing deletion...").removeClass("text-red-500").addClass("text-blue-500");
        progressContainer.removeClass("hidden");

        // Prepare deletion
        $.post(diu_ajax_params.ajax_url, {
            action: "diu_prepare_deletion",
            nonce: diu_ajax_params.nonce,
            user_role: userRole,
            cutoff_date: cutoffDate,
        })
            .done(function (response) {
                if (response.success) {
                    statusMessage.text(`Found ${response.data.total} users. Starting deletion...`);
                    processBatch(response.data.total);
                } else {
                    statusMessage.text(response.data || "Error preparing deletion.").addClass("text-red-500");
                    progressContainer.addClass("hidden");
                }
            })
            .fail(function () {
                statusMessage.text("Failed to prepare deletion.").addClass("text-red-500");
                progressContainer.addClass("hidden");
            });
    });

    function processBatch(totalUsers) {
        let processed = 0;

        function processNextBatch() {
            $.post(diu_ajax_params.ajax_url, {
                action: "diu_process_batch",
                nonce: diu_ajax_params.nonce,
            })
                .done(function (response) {
                    if (response.success) {
                        processed += 50;
                        const remaining = response.data.remaining;
                        const percentage = Math.min(((processed / totalUsers) * 100).toFixed(2), 100);
                        progressBar.val(percentage);

                        statusMessage.text(
                            `Processing... ${processed} of ${totalUsers} users deleted (${percentage}%).`
                        );

                        if (remaining > 0) {
                            processNextBatch();
                        } else {
                            statusMessage.text("Deletion complete!").addClass("text-green-500");
                            progressContainer.addClass("hidden");
                        }
                    } else {
                        statusMessage.text(response.data || "Error during batch processing.").addClass("text-red-500");
                        progressContainer.addClass("hidden");
                    }
                })
                .fail(function () {
                    statusMessage.text("Failed to process batch.").addClass("text-red-500");
                    progressContainer.addClass("hidden");
                });
        }

        processNextBatch();
    }
});
