jQuery(document).ready(function($) {
    var pcount = 0;

    $('#check').on('click', function(event) {
        event.preventDefault();
        console.log("I am here");
        // Get the user's input
        var max = $('#max-number').val();
        
        // Make an AJAX request
        $.ajax({
            type: 'POST',
            url: primeCheckAjax.ajaxurl,
            data: {
                action: 'get_prime_numbers',
                max: max
            },
            success: function(response) {
                console.log("response received", response);
                $('#prime-numbers').html(response);

                if (response.includes("it is the right answer")) {
                    pcount++;
                    console.log("pcount incremented to", pcount);

                    // Store the score in the database via AJAX
                    $.ajax({
                        type: 'POST',
                        url: primeCheckAjax.ajaxurl,
                        data: {
                            action: 'update_score',
                            score:1,
                            max : max
                        },
                        success: function(scoreResponse) {
                            console.log("Score updated in the database.");
                        }
                    });

                    $("#score").html("The score is: " + pcount);
                    console.log("Display pcount", pcount);
                }
            }
        });
    });

    $('#check-score').on('click', function(event) {
        event.preventDefault();
        // Make an AJAX request to check the total score
        $.ajax({
            type: 'POST',
            url: primeCheckAjax.ajaxurl,
            data: {
                action: 'check_total_score'
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.total_score !== null) {
                    $('#totalscore').html('Total Score for Prime Numbers: ' + data.total_score);
                } else {
                    $('#totalscore').html('No prime numbers found in your list.');
                }
            }
        });
    });
});