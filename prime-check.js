jQuery(document).ready(function($) {
    
    var numArr = []; 
    var pcount = 0;


    //GET request to acccess the db values
    
    function getdb(){
        $.ajax({
            type: 'GET',
            url: primeCheckAjax.ajaxurl,
            data: {
                action:'db_check'
            },
            success: function(response) {
                // Handle the fetched data (assuming it's an array of prime numbers)
                // You can store this data in a variable for later use
                numArr = JSON.parse(response);
                console.log("Data From The Database"); // Log the retrieved data to the console
                console.log(numArr);
                if(numArr.length > 0)
                {
                    fetchTotalScore();
                }
                else{
                        $('#prime-numbers').html('No prime numbers found in the database.');
                    }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                // You can display an error message to the user here
            }
        });
    }
    
    function UserInput(){
    $('#check').on('click', function(event) {
        event.preventDefault();                              
        
        console.log(numArr);
        

        console.log(" primeCheckAjax.ajaxurl", primeCheckAjax.ajaxurl)

        // Get the user's input
        var inputNum = $('#max-number').val();

        // Convert num to an integer for comparison
        var num = parseInt(inputNum);
        console.log(num);
            if (isNaN(num)){
                $('#prime-numbers').html("Invalid input");
            }
            else {
                
                if (num < 0){
                    $("#number-entered").html("The number entered is invalid. Enter a positive number.");
                }
                
                else if (num == 0 || num == 1){
                    $("#number-entered").html("The number entered is shouldn't be a 0 or 1 ");   
                }
        
                else  {
                    
                    console.log("prime Database:", numArr); 
    

                     var existsInData = numArr.indexOf(num.toString()) !== -1; // Check if the number exists in the array
                     

                     if(existsInData)
                     {
                             $('#prime-numbers').html("Number "+num+" already exists in your list");
                     }
                     else{
                       
                        var isPrime = true;
                        for (var i = 2; i * i <= num; i++) {
                            if (num % i == 0) {
                                isPrime = false;
                                break;
                            }
                        }
                        if(isPrime)
                        {
                            // Make an AJAX request to insert the number into the database
                                    // Store the score in the database via AJAX
                            $.ajax({
                                type: 'POST',
                                url: primeCheckAjax.ajaxurl, // Replace with the actual path to your PHP script
                                data: {
                                    action: 'insert_prime_number',
                                    max: num
                                },
                                success: function(response) {
                                    $('#prime-numbers').html("Yes, it is the right answer!");
                                    pcount++;
                                    console.log("pcount incremented to ", pcount);
                                    numArr.push(num.toString());
                                    fetchTotalScore();
                                },
                                // Add an error callback for error handling
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error("AJAX Error:", textStatus, errorThrown);
                                        // You can display an error message to the user here
                                    }
                             });
                        }
                        else {
                                $('#prime-numbers').html("Oh No! Try again,");
                            
                        } 
                        
                    }

                }
            }
       
           
            
            
    });
}

           

function fetchTotalScore() {
    //GET request to acccess the SCORE
    $.ajax({
        type: 'GET',
        url: primeCheckAjax.ajaxurl,
        data: {
            action: 'score_check',
        },
        success: function(response) {


            // display total score
           // $("#score1").html("Total score is : " + response);
       
           var score = JSON.parse(response);   
            console.log(score);
            // console.log(sum_score);


            if (score.total_score !== null) {
                $('#totalscore').html('Total Score for Prime Numbers: ' + score.total_score );
            } else {
                $('#totalscore').html('No prime numbers found in your list.');
            }
            
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
            // You can display an error message to the user here
        }
    });
}


    
    getdb();
        
    UserInput();
});
//test