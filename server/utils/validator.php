<?php

/**
 * Kiểm tra nội dung câu hỏi
 */
function validateQuestionContent($content, $part = null)
{
	if ($part == 1) {
		if (empty($content)) return true;
	} else {
		if (!isset($content) || empty(trim($content))) {
			throw new InvalidArgumentException("Nội dung câu hỏi không được để trống.");
		}
	}

	if (!empty($content)) {
		$length = mb_strlen(trim($content), 'UTF-8');
		if ($length < 5) {
			throw new InvalidArgumentException("Nội dung câu hỏi quá ngắn (tối thiểu 5 ký tự).");
		}
		if ($length > 5000) {
			throw new InvalidArgumentException("Nội dung câu hỏi quá dài (tối đa 5000 ký tự).");
		}
	}
	return true;
}

/**
 * Kiểm tra mảng đáp án
 */
function validateOptions($options, $part = null)
{
	$expectedCount = ($part === '2' || $part === 2) ? 3 : 4;
	if (!is_array($options) || count($options) !== $expectedCount) {
		throw new InvalidArgumentException("Câu hỏi phải có chính xác $expectedCount đáp án.");
	}

	$labels = ['A', 'B', 'C', 'D'];
	$index = 0;
	foreach ($options as $option) {
		$content = is_array($option) ? ($option['content'] ?? '') : $option;
		if (empty(trim($content))) {
			$label = $labels[$index] ?? '?';
			throw new InvalidArgumentException("Nội dung của đáp án {$label} không được để trống.");
		}
		if (mb_strlen(trim($content), 'UTF-8') < 1) {
			$label = $labels[$index] ?? '?';
			throw new InvalidArgumentException("Nội dung của đáp án {$label} quá ngắn.");
		}
		$index++;
	}
	return true;
}

/**
 * Kiểm tra đáp án đúng
 */
function validateCorrectAnswer($answer)
{
	if (empty($answer)) {
		throw new InvalidArgumentException("Chưa chọn đáp án đúng cho câu hỏi.");
	}
	$normalizedAnswer = strtoupper(trim($answer));
	$validOptions = ['A', 'B', 'C', 'D'];
	if (!in_array($normalizedAnswer, $validOptions, true)) {
		throw new InvalidArgumentException("Đáp án đúng không hợp lệ (chỉ chấp nhận A, B, C, hoặc D).");
	}
	return true;
}

/**
 * Kiểm tra Test (Đề thi) có tồn tại không
 */
function validateTestExists(PDO $db, $testId)
{
	if (!is_numeric($testId) || $testId <= 0) {
		throw new InvalidArgumentException("ID của bài test không hợp lệ.");
	}
	$sql = "SELECT id FROM tests WHERE id = :id AND is_active = 1 LIMIT 1";
	$stmt = $db->prepare($sql);
	$stmt->execute([':id' => $testId]);
	if (!$stmt->fetchColumn()) {
		throw new Exception("Bài test không tồn tại hoặc đã bị vô hiệu hóa.");
	}
	return true;
}

/**
 * Kiểm tra Part (Phần thi TOEIC)
 */
function validateToeicPart($part)
{
	if (!is_numeric($part) || $part < 1 || $part > 7) {
		throw new InvalidArgumentException("Toeic Part phải là số từ 1 đến 7.");
	}
	return true;
}

/**
 * Kiểm tra Question Number
 */
function validateQuestionNumber($questionNumber, $part = null)
{
	if (!is_numeric($questionNumber) || $questionNumber <= 0) {
		throw new InvalidArgumentException("Số thứ tự câu hỏi phải là số dương.");
	}
	if ($questionNumber > 200) {
		throw new InvalidArgumentException("Số thứ tự câu hỏi không được vượt quá 200.");
	}
	return true;
}

/**
 * Kiểm tra Passage ID
 */
function validatePassageExists(PDO $db, $passageId, $testId)
{
	if (empty($passageId)) return true;
	if (!is_numeric($passageId) || $passageId <= 0) {
		throw new InvalidArgumentException("ID của passage không hợp lệ.");
	}
	$sql = "SELECT id FROM passages WHERE id = :id AND test_id = :test_id LIMIT 1";
	$stmt = $db->prepare($sql);
	$stmt->execute([':id' => $passageId, ':test_id' => $testId]);
	if (!$stmt->fetchColumn()) {
		throw new Exception("Passage không tồn tại hoặc không thuộc bài test này.");
	}
	return true;
}

/**
 * Kiểm tra Audio URL
 */
function validateAudioUrl($url)
{
	if (empty($url)) return true;
	if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('#^/uploads/audio/.*\.(mp3|wav|ogg|m4a)$#i', $url)) {
		throw new InvalidArgumentException("Định dạng URL audio không hợp lệ.");
	}
	return true;
}

/**
 * Kiểm tra Image URL
 */
function validateImageUrl($url)
{
	if (empty($url)) return true;
	if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('#^/uploads/image/.*\.(jpg|jpeg|png|gif)$#i', $url)) {
		throw new InvalidArgumentException("Định dạng URL hình ảnh không hợp lệ.");
	}
	return true;
}

/**
 * Kiểm tra Explanation
 */
function validateExplanation($explanation)
{
	if (empty($explanation)) return true;
	if (mb_strlen(trim($explanation), 'UTF-8') > 5000) {
		throw new InvalidArgumentException("Giải thích quá dài (tối đa 5000 ký tự).");
	}
	return true;
}

/**
 * Validate Part-specific requirements
 */
function validatePartSpecificRequirements($part, $data)
{
	$part = (int) $part;
	switch ($part) {
		case 1:
			if (empty($data['image_url'])) throw new InvalidArgumentException("Part 1 yêu cầu phải có hình ảnh.");
			break;
		case 2: case 3: case 4:
			if (empty($data['audio_url'])) throw new InvalidArgumentException("Part {$part} yêu cầu phải có âm thanh.");
			if (empty($data['content'])) throw new InvalidArgumentException("Part {$part} yêu cầu phải có nội dung câu hỏi.");
			break;
		case 5: case 6:
			if (empty($data['content'])) throw new InvalidArgumentException("Part {$part} yêu cầu phải có nội dung câu hỏi.");
			break;
		case 7:
			if (empty($data['passage_id'])) throw new InvalidArgumentException("Part 7 yêu cầu phải chọn passage.");
			if (empty($data['content'])) throw new InvalidArgumentException("Part {$part} yêu cầu phải có nội dung câu hỏi.");
			break;
		default:
			throw new InvalidArgumentException("Part không hợp lệ: {$part}");
	}
	return true;
}