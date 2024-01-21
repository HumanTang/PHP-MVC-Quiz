<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Application with AJAX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
        }
        .correct-answer {
            color: green;
            font-weight: bold;
        }
        .incorrect-answer {
            color: red;
            font-weight: bold;
        }
    </style>
    <link rel="stylesheet" href="./src/output.css">
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <div id="quiz-container"></div>
    <div id="result-container"></div>

    <script>
        $(document).ready(function() {
            const quizContainer = $('#quiz-container');
            const resultContainer = $('#result-container');

            // Load saved user answers from cookies
            const savedUserAnswers = getCookies();
            displayQuiz(savedUserAnswers);

            function displayQuiz(savedUserAnswers) {
                $.ajax({
                    url: 'quiz.json',
                    dataType: 'json',
                    contentType: "application/json; charset=utf-8",
                    success: function(questions) {                        
                        const form = $('<form>').attr({
                            method: 'post',
                            action: '<?php echo $_SERVER['PHP_SELF']; ?>'
                        });

                        questions.forEach((questionObj, index) => {
                            const fieldset = $('<fieldset>');
                            const legend = $('<legend>').text(questionObj.question);
                            fieldset.append(legend);

                            questionObj.options.forEach((option, optionIndex) => {
                                const label = $('<label>');
                                const input = $('<input>').attr({
                                    type: 'radio',
                                    name: `question_${index}`,
                                    value: optionIndex
                                });

                                // Check if user has a saved answer for this question
                                if (savedUserAnswers[index] === optionIndex.toString()) {
                                    input.prop('checked', true);
                                }

                                label.append(input, option);
                                fieldset.append(label, $('<br>'));
                            });

                            form.append(fieldset, $('<br>'));
                        });

                        const submitButton = $('<input>').attr({
                            type: 'submit',
                            value: 'Submit'
                        });

                        form.append(submitButton);

                        form.submit(function(event) {
                            event.preventDefault();
                            submitQuiz(questions);
                        });

                        quizContainer.html(form);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching quiz:', status, error);
                    }
                });
            }

            function submitQuiz(questions) {
                const formData = $('form').serializeArray();
                console.log('FormData:', formData);
                $.ajax({
                    url: 'checkAnswer.php',
                    type: 'post',
                    dataType: 'json',
                    data: formData,

                    success: function(result) {
                        // Save user answers to cookies
                        console.log('result', result)                        
                        displayResult(result, questions);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error submitting quiz:', status, error);
                    }
                });
            }

            function displayResult(result, questions) {
                resultContainer.html('<h2>Quiz Result:</h2>');
                console.log("result", result)
                console.log('result.answers', result.answers) 
                questions.forEach((questionObj, index) => {
                    const userAnswer = result.answers[index];
                    const correctAnswer = questionObj.correctAnswer;
                    console.log('userAnswer', result.answers[index]) 
                    const p = $('<p>').text(`Question ${index + 1}: ${questionObj.question}`);
                    
                    if (userAnswer == correctAnswer) {
                        p.addClass('correct-answer').append(` Your answer: ${questionObj.options[userAnswer]}`);

                    } else {
                        p.addClass('incorrect-answer').append(` Your answer: ${questionObj.options[userAnswer]}`);

                        p.append(` (Correct answer: ${questionObj.options[correctAnswer]})`);
                    }

                    resultContainer.append(p);
                });
            }

            function saveCookies(result) {
                // Save user answers to cookies
                const expirationDate = new Date();
                expirationDate.setFullYear(expirationDate.getFullYear() + 1);

                result.answers.forEach((answer, index) => {
                    document.cookie = `userAnswer_${index}=${answer}; expires=${expirationDate.toUTCString()}; path=/`;
                    console.log(document.cookie);
                });
            }

            function getCookies() {
                // Retrieve saved user answers from cookies
                const cookies = document.cookie.split(';');
                const savedUserAnswers = {};

                cookies.forEach(cookie => {
                    const [name, value] = cookie.trim().split('=');
                    const match = name.match(/^userAnswer_(\d+)$/);

                    if (match) {
                        savedUserAnswers[match[1]] = value;
                    }
                });

                return savedUserAnswers;
            }
        });
    </script>
</body>
</html>
