/**
 validation.js
 Chủ yếu để kiểm tra các thao tác trước khi submit data

 Flow:
 [Giao diện người dùng (DOM)] -> [validation.js] -> [form-handler.js / API call] -> [Backend Server]
 1. Kiểm tra logic số thứ tự: Không để trống, không trùng lặp trong cụm/đề, nằm trong khoảng [1-200]
 2. Kiểm tra nội dung: Bắt buộc nhập nội dung câu hỏi, đoạn văn (passage), và đầy đủ 4 lựa chọn
 3. Kiểm tra đáp án: Đảm bảo mỗi câu hỏi đều đã được chọn một đáp án đúng
 4. Kiểm tra Multimedia: 
 - Part 1: Bắt buộc có Image hoặc Audio
 - Part 2, 3, 4: Bắt buộc phải có Audio (File mới hoặc URL đã tồn tại)
 Kiểm tra:

 1. Số thứ tự câu hỏi:
 Phải có nhập, không được để trống
 Nằm trong khoảng từ 1 đến 200
 Không trùng lặp với các câu khác trong cùng một Part
 Không trùng lặp với các Part khác đã có trong đề (check qua AppState)

 2. Nội dung văn bản:
 Câu hỏi đơn/câu hỏi con phải có nội dung
 Các group phải có nội dung đoạn văn 
 Part 1, 3, 4: Phải nhập đầy đủ nội dung cho cả 4 đáp án (A, B, C, D)
 Part 2: Phải nhập đầy đủ nội dung cho 3 đáp án (A, B, C)
 
 3. Đáp án đúng:
 Mỗi câu hỏi (đơn hoặc con) bắt buộc phải được tick chọn 1 đáp án đúng qua Radio button

 4.Img/audio:
 Part 1: Bắt buộc có ảnh hoặc âm thanh
 Part 2, 3, 4: Bắt buộc phải có tệp âm thanh (chấp nhận file mới hoặc URL cũ đã tồn tại)

 5. Cấu trúc group:
 Mỗi cụm câu hỏi phải có ít nhất 1 câu hỏi con bên trong

 @param {NodeList|Array} blocks - Danh sách các block câu hỏi (đơn hoặc cụm) lấy từ DOM
 @param {string} part - Số thứ tự Part của đề thi (1-7) để áp dụng quy tắc validation tương ứng
 @returns {boolean} - Trả về true nếu tất cả dữ liệu hợp lệ, ngược lại trả về false và hiển thị thông báo lỗi
 */

function validateAllBlocks(blocks, part) {
    let isValid = true;
    const seenQuestionNumbers = new Set();

    const checkError = (condition, msg) => {
        if (condition) { showMessage(msg, 'error'); isValid = false; return true; }
        return false;
    };

    blocks.forEach((block, blockIndex) => {
        if (!isValid) return; 

        if (block.dataset.type === 'single') {
            const qNumStr = block.querySelector('.question-number')?.value.trim();
            if (checkError(!qNumStr, `Câu #${blockIndex + 1}: Vui lòng nhập số thứ tự câu hỏi`)) return;
            
            const qNum = parseInt(qNumStr);
            if (checkError(seenQuestionNumbers.has(qNum), `Câu #${blockIndex + 1}: Số thứ tự ${qNumStr} bị trùng lặp`)) return;
            if (checkError(AppState.allTestQuestionNumbers.has(qNum) && !block.dataset.questionId, `Câu #${blockIndex + 1}: Số thứ tự ${qNumStr} đã tồn tại trong đề`)) return;
            if (checkError(qNum < 1 || qNum > 200, `Câu #${blockIndex + 1}: Số thứ tự phải từ 1-200`)) return;
            seenQuestionNumbers.add(qNum);

            if (checkError(!block.querySelector('.question-content')?.value.trim(), `Câu #${blockIndex + 1}: Vui lòng nhập nội dung câu hỏi`)) return;

            const options = block.querySelectorAll('.option-content');
            const expectedOptionsCount = part === '2' ? 3 : 4;
            const optionsToCheck = Array.from(options).slice(0, expectedOptionsCount);
            optionsToCheck.forEach(opt => { checkError(!opt.value.trim(), `Câu #${blockIndex + 1}: Vui lòng nhập đầy đủ ${expectedOptionsCount} đáp án`); });
            if (!isValid) return;

            if (checkError(!block.querySelector('.correct-radio:checked'), `Câu #${blockIndex + 1}: Vui lòng chọn đáp án đúng`)) return;

            const hasMedia = (block.querySelector('.audio-file')?.files[0] || block.querySelector('.audio-file')?.dataset.existingUrl) || 
                             (block.querySelector('.image-file')?.files[0] || block.querySelector('.image-file')?.dataset.existingUrl);
            const hasAudio = (block.querySelector('.audio-file')?.files[0] || block.querySelector('.audio-file')?.dataset.existingUrl);
            
            if (checkError(part === '1' && !hasMedia, `Câu #${blockIndex + 1}: Part 1 cần hình ảnh hoặc âm thanh`)) return;
            if (checkError(['2', '3', '4'].includes(part) && !hasAudio, `Câu #${blockIndex + 1}: Part ${part} cần âm thanh`)) return;

        } else {
            if (checkError(!block.querySelector('.passage-content')?.value.trim(), `Cụm #${blockIndex + 1}: Vui lòng nhập nội dung đoạn văn`)) return;
            
            const subQs = block.querySelectorAll('.sub-question-item');
            if (checkError(subQs.length === 0, `Cụm #${blockIndex + 1}: Vui lòng thêm ít nhất 1 câu hỏi`)) return;

            const hasMedia = (block.querySelector('.group-audio-file')?.files[0] || block.querySelector('.group-audio-file')?.dataset.existingUrl) || 
                             (block.querySelector('.group-image-file')?.files[0] || block.querySelector('.group-image-file')?.dataset.existingUrl);
            const hasAudio = (block.querySelector('.group-audio-file')?.files[0] || block.querySelector('.group-audio-file')?.dataset.existingUrl);

            if (checkError(part === '1' && !hasMedia, `Cụm #${blockIndex + 1}: Part 1 cần hình ảnh hoặc âm thanh`)) return;
            if (checkError(['2', '3', '4'].includes(part) && !hasAudio, `Cụm #${blockIndex + 1}: Part ${part} cần âm thanh`)) return;

            subQs.forEach((subQ, subIndex) => {
                if (!isValid) return;
                const qNumStr = subQ.querySelector('.sub-question-number')?.value.trim();
                if (checkError(!qNumStr, `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Thiếu số thứ tự`)) return;
                
                const qNum = parseInt(qNumStr);
                if (checkError(seenQuestionNumbers.has(qNum), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Trùng số thứ tự ${qNumStr}`)) return;
                if (checkError(AppState.allTestQuestionNumbers.has(qNum) && !subQ.dataset.questionId, `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Số ${qNumStr} đã tồn tại`)) return;
                if (checkError(qNum < 1 || qNum > 200, `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Số thứ tự phải từ 1-200`)) return;
                seenQuestionNumbers.add(qNum);

                if (checkError(!subQ.querySelector('.question-content')?.value.trim(), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Thiếu nội dung`)) return;
                const subOptions = subQ.querySelectorAll('.option-content');
                const expectedSubOptionsCount = part === '2' ? 3 : 4;
                const subOptionsToCheck = Array.from(subOptions).slice(0, expectedSubOptionsCount);
                subOptionsToCheck.forEach(opt => { checkError(!opt.value.trim(), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Thiếu ${expectedSubOptionsCount} đáp án`); });
                if (checkError(!subQ.querySelector('input[type="radio"]:checked'), `Cụm #${blockIndex + 1}, Câu #${subIndex + 1}: Chưa chọn đáp án đúng`)) return;
            });
        }
    });
    return isValid;
}
