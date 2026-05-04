<?php

require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/../models/question.php';
require_once __DIR__ . '/../models/passage.php';
require_once __DIR__ . '/../utils/validator.php';
require_once __DIR__ . '/../utils/fileHandler.php';
require_once __DIR__ . '/../utils/response.php';

/**
 * tạo một câu hỏi mới với các tệp phương tiện tùy chọn
 */
function apiCreateQuestion(PDO $db)
{
	try {
		$testId = helperGetPostValue('test_id');
		$part = helperGetPostValue('part');
		$questionNumber = helperGetPostValue('question_number');
		$passageId = helperGetPostValue('passage_id');
		$content = helperGetPostValue('content');
		$correctAnswer = helperGetPostValue('correct_answer');
		$explanation = helperGetPostValue('explanation');
		$isSubQuestion = !empty($passageId);

		$options = json_decode(helperGetPostValue('options', '{}'), true);
		if (!is_array($options)) {
			throw new Exception("Định dạng đáp án không hợp lệ");
		}

		validateToeicPart($part);
		validateQuestionNumber($questionNumber, $part);
		validateQuestionContent($content, $part);
		validateCorrectAnswer($correctAnswer);
		validateOptions($options);

		if (!empty($explanation)) {
			validateExplanation($explanation);
		}

		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại hoặc không hoạt động");
		}

		if (!empty($passageId)) {
			validatePassageExists($db, $passageId, $testId);
		}

		$audioUrl = null;
		$imageUrl = null;

		if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$audioUrl = fh_upload_file($_FILES['audio_file'], 'audio');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload audio: " . $e->getMessage());
			}
		} else {
			$audioUrl = helperGetPostValue('audio_url');
		}

		if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$imageUrl = fh_upload_file($_FILES['image_file'], 'image');
			} catch (Exception $e) {
				if ($audioUrl && isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
					fh_delete_file($audioUrl);
				}
				throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
			}
		} else {
			$imageUrl = helperGetPostValue('image_url');
		}

		helperValidatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion);

		$questionData = [
			'test_id' => $internalTestId,
			'part' => $part,
			'question_number' => $questionNumber,
			'passage_id' => !empty($passageId) ? $passageId : null,
			'content' => !empty($content) ? trim($content) : null,
			'correct_answer' => strtoupper($correctAnswer),
			'audio_url' => $audioUrl,
			'image_url' => $imageUrl,
			'explanation' => !empty($explanation) ? trim($explanation) : null
		];

		$optionsData = [
			['label' => 'A', 'content' => $options['A']],
			['label' => 'B', 'content' => $options['B']],
			['label' => 'C', 'content' => $options['C']],
			['label' => 'D', 'content' => $options['D']]
		];

		$questionId = questionCreateWithOptions($db, $questionData, $optionsData);

		return [
			'success' => true,
			'message' => 'Câu hỏi đã được lưu thành công',
			'data' => [
				'question_id' => $questionId,
				'test_id' => $testId,
				'part' => $part,
				'question_number' => $questionNumber
			]
		];

	} catch (Exception $e) {
		if (isset($audioUrl))
			fh_delete_file($audioUrl);
		if (isset($imageUrl))
			fh_delete_file($imageUrl);

		return [
			'success' => false,
			'message' => $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

/**
 * tạo nhiều câu hỏi từ form
 */
function apiCreateQuestionsFromForm(PDO $db)
{
	try {
		$testId = helperGetPostValue('test_id');
		$part = helperGetPostValue('part');
		$questionsJson = helperGetPostValue('questions', '[]');

		$questions = json_decode($questionsJson, true);
		if (!is_array($questions)) {
			throw new Exception("Định dạng dữ liệu câu hỏi không hợp lệ");
		}

		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		validateToeicPart($part);

		$createdQuestions = [];
		$errors = [];

		foreach ($questions as $index => $questionData) {
			try {
				if (empty($questionData['question_number'])) {
					throw new Exception("Câu " . ($index + 1) . ": Thiếu số thứ tự câu hỏi");
				}

				if (empty($questionData['options'])) {
					throw new Exception("Câu " . ($index + 1) . ": Thiếu đáp án");
				}

				$qData = [
					'test_id' => $internalTestId,
					'part' => $part,
					'question_number' => $questionData['question_number'],
					'passage_id' => $questionData['passage_id'] ?? null,
					'content' => $questionData['content'] ?? null,
					'correct_answer' => strtoupper($questionData['correct_answer'] ?? ''),
					'audio_url' => $questionData['audio_url'] ?? null,
					'image_url' => $questionData['image_url'] ?? null,
					'explanation' => $questionData['explanation'] ?? null
				];

				validateQuestionContent($qData['content'], $part);
				validateCorrectAnswer($qData['correct_answer']);
				validateOptions($questionData['options']);

				$questionId = questionCreate($db, $qData);
				$createdQuestions[] = [
					'question_id' => $questionId,
					'question_number' => $qData['question_number']
				];

			} catch (Exception $e) {
				$errors[] = "Câu " . ($index + 1) . ": " . $e->getMessage();
			}
		}

		$response = [
			'success' => count($errors) === 0,
			'message' => count($createdQuestions) . ' câu hỏi được tạo thành công',
			'created_count' => count($createdQuestions),
			'created_questions' => $createdQuestions
		];

		if (count($errors) > 0) {
			$response['errors'] = $errors;
			$response['message'] .= ' (' . count($errors) . ' lỗi)';
		}

		return $response;

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => 'Lỗi: ' . $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

/**
 * lấy danh sách câu hỏi cho một bài thi
 */
function apiGetQuestions(PDO $db, $testId)
{
	try {
		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		$sql = "SELECT * FROM questions 
                WHERE test_id = :test_id 
                ORDER BY part ASC, question_number ASC";

		$stmt = $db->prepare($sql);
		$stmt->execute([':test_id' => $internalTestId]);
		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$optionsSql = "SELECT id, label, content FROM options WHERE question_id = :question_id ORDER BY label";
		$optionsStmt = $db->prepare($optionsSql);

		foreach ($questions as &$question) {
			$optionsStmt->execute([':question_id' => $question['id']]);
			$question['options'] = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
		}

		return [
			'success' => true,
			'data' => $questions,
			'count' => count($questions)
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * lấy thông tin một câu hỏi
 */
function apiGetQuestion(PDO $db, $questionId)
{
	try {
		$sql = "SELECT * FROM questions WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->execute([':id' => $questionId]);
		$question = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$question) {
			throw new Exception("Câu hỏi không tồn tại");
		}

		$optionsSql = "SELECT id, label, content FROM options WHERE question_id = :question_id ORDER BY label";
		$optionsStmt = $db->prepare($optionsSql);
		$optionsStmt->execute([':question_id' => $questionId]);
		$question['options'] = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);

		return [
			'success' => true,
			'data' => $question
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * tạo một đoạn văn mới
 */
function apiCreatePassage(PDO $db)
{
	try {
		$testId = helperGetPostValue('test_id');
		$part = helperGetPostValue('part');
		$content = helperGetPostValue('content');

		$internalTestId = helperGetInternalTestId($db, $testId);
		if (empty($testId))
			throw new Exception("test_id là bắt buộc");
		if (!$internalTestId)
			throw new Exception("Đề thi không tồn tại");

		$audioUrl = null;
		$imageUrl = null;

		if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$audioUrl = fh_upload_file($_FILES['audio_file'], 'audio');
			} catch (Exception $e) {
				throw new Exception("Lỗi upload audio: " . $e->getMessage());
			}
		} else {
			$audioUrl = helperGetPostValue('audio_url');
		}

		if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
			try {
				$imageUrl = fh_upload_file($_FILES['image_file'], 'image');
			} catch (Exception $e) {
				if ($audioUrl)
					fh_delete_file($audioUrl);
				throw new Exception("Lỗi upload hình ảnh: " . $e->getMessage());
			}
		} else {
			$imageUrl = helperGetPostValue('image_url');
		}

		if (!empty($part)) {
			helperValidatePassageMediaRequirements($part, $audioUrl, $imageUrl);
		}

		$passageData = [
			'test_id' => $internalTestId,
			'content' => !empty($content) ? trim($content) : null,
			'audio_url' => $audioUrl,
			'image_url' => $imageUrl
		];

		$passageId = passageCreate($db, $passageData);

		return [
			'success' => true,
			'message' => 'Đoạn văn đã được tạo thành công',
			'data' => [
				'passage_id' => $passageId,
				'test_id' => $testId
			]
		];

	} catch (Exception $e) {
		if (isset($audioUrl))
			fh_delete_file($audioUrl);
		if (isset($imageUrl))
			fh_delete_file($imageUrl);

		return [
			'success' => false,
			'message' => $e->getMessage(),
			'code' => 'VALIDATION_ERROR'
		];
	}
}

/**
 * lấy các đoạn văn của một bài thi
 */
function apiGetPassages(PDO $db, $testId)
{
	try {
		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		$passages = passageGetByTestId($db, $internalTestId);

		return [
			'success' => true,
			'data' => $passages,
			'count' => count($passages)
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * xóa một câu hỏi
 */
function apiDeleteQuestion(PDO $db, $questionId)
{
	try {
		$question = questionGetById($db, $questionId);

		if (!$question)
			throw new Exception("Câu hỏi không tồn tại");

		if ($question['audio_url'])
			fh_delete_file($question['audio_url']);
		if ($question['image_url'])
			fh_delete_file($question['image_url']);

		questionDelete($db, $questionId);

		return [
			'success' => true,
			'message' => 'Câu hỏi đã được xóa thành công'
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * xóa một đoạn văn
 */
function apiDeletePassage(PDO $db, $passageId)
{
	try {
		$passage = passageGetById($db, $passageId);
		if (!$passage)
			throw new Exception("Đoạn văn không tồn tại");

		// Xóa file của passage
		if ($passage['audio_url'])
			fh_delete_file($passage['audio_url']);
		if ($passage['image_url'])
			fh_delete_file($passage['image_url']);

		// Lấy danh sách câu hỏi thuộc passage này để dọn dẹp file
		$sql = "SELECT id, audio_url, image_url FROM questions WHERE passage_id = :passage_id";
		$stmt = $db->prepare($sql);
		$stmt->execute([':passage_id' => $passageId]);
		$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($questions as $q) {
			if ($q['audio_url'])
				fh_delete_file($q['audio_url']);
			if ($q['image_url'])
				fh_delete_file($q['image_url']);

			// Xóa options (nếu DB không có ON DELETE CASCADE)
			$db->prepare("DELETE FROM options WHERE question_id = ?")->execute([$q['id']]);
		}

		// Xóa các câu hỏi
		$db->prepare("DELETE FROM questions WHERE passage_id = ?")->execute([$passageId]);

		// Cuối cùng xóa passage
		passageDelete($db, $passageId);

		return [
			'success' => true,
			'message' => 'Đoạn văn đã được xóa thành công'
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage()
		];
	}
}

/**
 * import nhiều câu hỏi từ file JSON
 */
function apiImportQuestions(PDO $db)
{
	try {
		// Lấy dữ liệu từ form
		$testId = helperGetPostValue('test_id');
		$questionsDataJson = helperGetPostValue('questions_data', '[]');

		// Kiểm tra dữ liệu 
		if (empty($testId)) {
			throw new Exception("test_id là bắt buộc");
		}

		$questionsData = json_decode($questionsDataJson, true);
		if (!is_array($questionsData)) {
			throw new Exception("Định dạng JSON không hợp lệ");
		}

		$internalTestId = helperGetInternalTestId($db, $testId);
		if (!$internalTestId) {
			throw new Exception("Đề thi không tồn tại");
		}

		$db->beginTransaction();

		$importedCount = 0;
		$errors = [];

		// Xử lý mỗi item (mỗi item chứa 1 passage + nhiều questions)
		foreach ($questionsData as $itemIndex => $item) {
			try {
				$passageId = null;

				// Tạo passage nếu có
				if (!empty($item['passage']) && is_array($item['passage'])) {
					$passageData = [
						'test_id' => $internalTestId,
						'content' => $item['passage']['content'] ?? null,
						'audio_url' => $item['passage']['audio_url'] ?? null,
						'image_url' => $item['passage']['image_url'] ?? null
					];

					$passageId = passageCreate($db, $passageData);
				}

				// Xử lý các câu hỏi
				if (!empty($item['questions']) && is_array($item['questions'])) {
					foreach ($item['questions'] as $qIndex => $questionData) {
						try {
							// Kiem tra các trường bắt buộc
							if (empty($questionData['part'])) {
								throw new Exception("Thiếu part");
							}
							if (empty($questionData['question_number'])) {
								throw new Exception("Thiếu question_number");
							}
							if (empty($questionData['content'])) {
								throw new Exception("Thiếu content");
							}
							if (empty($questionData['correct_answer'])) {
								throw new Exception("Thiếu correct_answer");
							}
							if (empty($questionData['options']) || !is_array($questionData['options'])) {
								throw new Exception("Thiếu options hoặc không phải mảng");
							}

							// Kiem tra tính hợp lệ của dữ liệu
							validateToeicPart($questionData['part']);

							validateQuestionNumber($questionData['question_number'], $questionData['part']);

							validateQuestionContent($questionData['content'], $questionData['part']);

							$correctAnswer = strtoupper($questionData['correct_answer']);
							validateCorrectAnswer($correctAnswer);

							validateOptions($questionData['options']);

							// Tạo câu hỏi
							$qData = [
								'test_id' => $internalTestId,
								'passage_id' => $passageId,
								'part' => $questionData['part'],
								'question_number' => $questionData['question_number'],
								'content' => $questionData['content'],
								'audio_url' => $questionData['audio_url'] ?? null,
								'image_url' => $questionData['image_url'] ?? null,
								'correct_answer' => $correctAnswer,
								'explanation' => $questionData['explanation'] ?? null
							];

							$questionId = questionCreate($db, $qData);

							// Thêm các options
							questionAddOptions($db, $questionId, $questionData['options']);

							$importedCount++;

						} catch (Exception $e) {
							$errors[] = "Item " . ($itemIndex + 1) . ", Question " . ($qIndex + 1) . ": " . $e->getMessage();
						}
					}
				}

			} catch (Exception $e) {
				$errors[] = "Item " . ($itemIndex + 1) . ": " . $e->getMessage();
			}
		}

		if (count($errors) > 0) {
			$db->rollBack();
			throw new Exception("Có lỗi trong quá trình import: " . implode("; ", $errors));
		}

		$db->commit();

		return [
			'success' => true,
			'message' => "Nhập thành công $importedCount câu hỏi",
			'imported_count' => $importedCount
		];

	} catch (Exception $e) {
		return [
			'success' => false,
			'message' => $e->getMessage(),
			'code' => 'IMPORT_ERROR'
		];
	}
}


/**
 * ==========================================
 * CÁC HÀM TIỆN ÍCH NỘI BỘ (HELPER FUNCTIONS)
 * ==========================================
 */

function helperGetPostValue($key, $default = null)
{
	return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function helperGetInternalTestId(PDO $db, $uuid)
{
	try {
		$sql = "SELECT id FROM tests WHERE uuid = :uuid AND is_active = 1";
		$stmt = $db->prepare($sql);
		$stmt->execute([':uuid' => $uuid]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result ? (int)$result['id'] : false;
	} catch (Exception $e) {
		return false;
	}
}

function helperValidatePassageMediaRequirements($part, $audioUrl, $imageUrl)
{
	$part = intval($part);
	switch ($part) {
		case 1:
			if (empty($imageUrl) && empty($audioUrl))
				throw new Exception("Part 1: Cần có ít nhất hình ảnh hoặc âm thanh");
			break;
		case 2:
		case 3:
		case 4:
			if (empty($audioUrl))
				throw new Exception("Part $part: Âm thanh là bắt buộc cho cụm câu hỏi");
			break;
	}
}

function helperValidatePartRequirements($part, $content, $audioUrl, $imageUrl, $passageId, $isSubQuestion = false)
{
	$part = intval($part);
	switch ($part) {
		case 1:
			if (!$isSubQuestion && empty($imageUrl) && empty($audioUrl))
				throw new Exception("Part 1: Cần có ít nhất hình ảnh hoặc âm thanh");
			break;
		case 2:
		case 3:
		case 4:
			if (!$isSubQuestion && empty($audioUrl))
				throw new Exception("Part $part: Âm thanh là bắt buộc");
			if (empty($content))
				throw new Exception("Part $part: Nội dung câu hỏi là bắt buộc");
			break;
		case 5:
		case 6:
		case 7:
			if (empty($content))
				throw new Exception("Part $part: Nội dung câu hỏi là bắt buộc");
			break;
	}
}

?>