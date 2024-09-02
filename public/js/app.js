window.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('.sidebar__input')
    const folderBtn = document.querySelector('.sidebar__btn:nth-child(1)') 
    const fileBtn = document.querySelector('.sidebar__btn:nth-child(2)')
    const deleteBtn = document.querySelector('.sidebar__btn:nth-child(3)')
    const downloadBtn = document.querySelector('.content__btn')

    function uploadButton() {
        const isInputValue = !input.value.trim();

        folderBtn.disabled = isInputValue;
        fileBtn.disabled = isInputValue;
        deleteBtn.disabled = isInputValue;
        downloadBtn.disabled = true;
    }

    input.addEventListener('input', uploadButton)

    uploadButton()
})