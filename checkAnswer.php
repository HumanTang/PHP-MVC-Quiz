<?php
header('Content-Type: application/json');
error_log(print_r($_POST, true));
// Retrieve user answers from POST data
$userAnswers = $_POST;

// Read quiz questions and correct answers from quiz.json
$quizData = json_decode(file_get_contents('quiz.json'), true);

// Process user answers
$result = [];
foreach ($userAnswers as $questionIndex => $selectedOption) {
    $questionIndex = str_replace('question_', '', $questionIndex); // Extract the question index
    $correctAnswerIndex = $quizData[$questionIndex]['correctAnswer'];

    // Compare the user's answer with the correct answer index
    $result[] = ($selectedOption == $correctAnswerIndex);
}

// Return a response (this is just an example, replace with your logic)
$response = [
    'status' => 'success',
    'message' => 'Answers processed successfully.',
    'result' => $result,
    'answers' => array_values($userAnswers)
];

echo json_encode($response);
?>
