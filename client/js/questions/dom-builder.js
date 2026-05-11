/**
 * dom-builder.js - Quản lý việc thêm/xóa các khối câu hỏi trên giao diện
 */

function addBlock(type) {
	const testSelect = document.getElementById('testSelect');
	const partSelect = document.getElementById('partSelect');
	if (!testSelect || !partSelect) return;

	const testId = testSelect.value;
	const part = partSelect.value;

	if (!testId) return showMessage('Vui lòng chọn đề thi trước', 'error');
	if (!part) return showMessage('Vui lòng chọn part trước', 'error');

	AppState.globalBlockCounter++;
	const container = document.getElementById('questions-container');
	const templateId = type === 'single' ? 'single-question-template' : 'group-question-template';
	const template = document.getElementById(templateId);
	if (!template) return;

	const clone = template.content.cloneNode(true);
	const blockDiv = clone.querySelector('.question-block');
	
	blockDiv.dataset.blockId = AppState.globalBlockCounter;
	const nextNumber = getLastQuestionNumber() + 1;

	if (type === 'single') {
		const numberInput = blockDiv.querySelector('.question-number');
		if (numberInput) numberInput.value = nextNumber;
		blockDiv.querySelectorAll('.correct-radio').forEach(r => r.name = `correct_block_${AppState.globalBlockCounter}`);
	} else {
		const subContainer = blockDiv.querySelector('.sub-questions-container');
		for (let i = 0; i < 3; i++) {
			subContainer.appendChild(createSubQuestionDOM(AppState.globalBlockCounter, nextNumber + i));
		}
	}

	container.appendChild(clone);
	updateMediaBadges(blockDiv, part);
	updateQuestionCount();
}

function createSubQuestionDOM(blockId, questionNumber = null) {
	const template = document.getElementById('sub-question-template');
	const clone = template.content.cloneNode(true);
	const div = clone.querySelector('.sub-question-item');
	
	const subId = Date.now() + Math.floor(Math.random() * 1000);
	const radioName = `correct_group_${blockId}_sub_${subId}`;

	if (questionNumber) {
		const numIn = div.querySelector('.sub-question-number');
		if (numIn) numIn.value = questionNumber;
	}

	div.querySelectorAll('.correct-radio').forEach(r => r.name = radioName);
	
	return div;
}

function addSubQuestionBtn(button) {
	const blockDiv = button.closest('.question-block');
	const subContainer = blockDiv.querySelector('.sub-questions-container');
	const nextNumber = getLastQuestionNumber() + 1;
	subContainer.appendChild(createSubQuestionDOM(blockDiv.dataset.blockId, nextNumber));
	updateQuestionCount();
}

function removeSubQuestion(button) {
	const subQuestion = button.closest('.sub-question-item');
	const block = subQuestion.closest('.question-block');
	subQuestion.remove();

	if (block.querySelectorAll('.sub-question-item').length === 0) {
		block.remove();
	}
	
	updateQuestionCount();
}

function removeBlock(button) {
	button.closest('.question-block').remove();
	updateQuestionCount();
}

function deleteAllBlocks() {
	const container = document.getElementById('questions-container');
	if (container) container.innerHTML = '';
	AppState.globalBlockCounter = 0;
	updateQuestionCount();
}
