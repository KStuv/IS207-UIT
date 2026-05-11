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
	
	let templateId;
	if (type === 'single') {
		templateId = part === '2' ? 'single-question-template-3options' : 'single-question-template-4options';
	} else {
		templateId = part === '2' ? 'group-question-template-3options' : 'group-question-template-4options';
	}
	
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
			subContainer.appendChild(createSubQuestionDOM(AppState.globalBlockCounter, nextNumber + i, part));
		}
	}

	container.appendChild(clone);
	updateMediaBadges(blockDiv, part);
	updateQuestionCount();
}

function createSubQuestionDOM(blockId, questionNumber = null, part = null) {
	const templateId = part === '2' ? 'sub-question-template-3options' : 'sub-question-template-4options';
	const template = document.getElementById(templateId);
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
	const partSelect = document.getElementById('partSelect');
	const part = partSelect ? partSelect.value : null;
	subContainer.appendChild(createSubQuestionDOM(blockDiv.dataset.blockId, nextNumber, part));
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
