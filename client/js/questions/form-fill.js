/**
 * form-fill.js - Điền dữ liệu vào form từ API (Dùng khi sửa bài thi)
 */

/**
 * Helper dùng chung để điền các trường cơ bản của 1 câu hỏi (Số câu, Nội dung, Đáp án, Giải thích)
 */
function fillBaseQuestionUI(container, question) {
	const setVal = (sel, val) => { const el = container.querySelector(sel); if(el) el.value = val || ''; };
	
	const qNumClass = container.classList.contains('sub-question-item') ? '.sub-question-number' : '.question-number';
	setVal(qNumClass, question.question_number);
	setVal('.question-content', question.content);
	setVal('.explanation', (question.explanation && question.explanation !== 'null') ? question.explanation : '');

	const optionInputs = container.querySelectorAll('.option-content');
	if (question.options && (question.options.length === 3 || question.options.length === 4)) {
		question.options.forEach((opt, idx) => { if (optionInputs[idx]) optionInputs[idx].value = opt.content || ''; });
	}

	container.querySelectorAll('.correct-radio').forEach(radio => {
		if (radio.value === question.correct_answer) radio.checked = true;
	});

	container.dataset.questionId = question.id;
	AppState.loadedQuestionIds.add(question.id);
}

function fillSingleQuestionData(question, block) {
	if (!block) return;
	
	fillBaseQuestionUI(block, question);

	const mediaSection = block.querySelector('.media-upload-section');
	if (mediaSection) {
		const updateMedia = (idx, url, type) => {
			const input = mediaSection.querySelector(`.upload-item:nth-child(${idx}) input[type="file"]`);
			const preview = mediaSection.querySelector(`.upload-item:nth-child(${idx}) .preview-container`);
			if (input) input.dataset.existingUrl = url;
			if (preview && url) {
				preview.innerHTML = type === 'image' 
					? `<img src="${url}" alt="Question image" style="max-width: 200px;">`
					: `<audio controls src="${url}" style="width: 100%;"></audio>`;
			}
		};
		updateMedia(1, question.image_url, 'image');
		updateMedia(2, question.audio_url, 'audio');
	}
}

function fillGroupQuestionData(passage, subQuestions, block) {
	if (!block) return;

	const passageInput = block.querySelector('.passage-content');
	if (passageInput) passageInput.value = passage.content || '';

	const updateGroupMedia = (selector, url, type) => {
		const input = block.querySelector(selector);
		const preview = input?.closest('.upload-item')?.querySelector('.preview-container');
		if (input) input.dataset.existingUrl = url;
		if (preview && url) {
			preview.innerHTML = type === 'image'
				? `<img src="${url}" alt="Passage image" style="max-width: 200px;">`
				: `<audio controls src="${url}" style="width: 100%;"></audio>`;
		}
	};
	updateGroupMedia('.group-image-file', passage.image_url, 'image');
	updateGroupMedia('.group-audio-file', passage.audio_url, 'audio');

	block.dataset.passageId = passage.id;
	AppState.loadedPassageIds.add(passage.id);

	const subContainer = block.querySelector('.sub-questions-container');
	if (subContainer) {
		subContainer.innerHTML = '';
		if (subQuestions) {
			subQuestions.forEach((subQ, idx) => {
				const subDiv = createSubQuestionDOM(block.dataset.blockId, subQ.question_number || idx + 1);
				fillBaseQuestionUI(subDiv, subQ);
				subContainer.appendChild(subDiv);
			});
		}
	}
}
