window.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('.sidebar__input');
    const folderBtn = document.querySelector('.sidebar__btn:nth-child(1)');
    const fileBtn = document.querySelector('.sidebar__btn:nth-child(2)');
    const deleteBtn = document.querySelector('.sidebar__btn:nth-child(4)');
    const downloadBtn = document.querySelector('.content__btn');
    const selectedPathText = document.querySelector('.content__title');
    const previewImage = document.querySelector('.content__bottom img');
    const errorSpan = document.querySelector('.sidebar__error');
    const errorUploadFile = document.querySelector('.sidebar__error-file');

    let selectedDirectoryId = null;
    let selectedFileId = null;

    function uploadButton() {
        const isInputValue = !input.value.trim();

        folderBtn.disabled = isInputValue || selectedFileId !== null;
        fileBtn.disabled = !selectedDirectoryId || selectedFileId !== null;
        input.disabled = selectedFileId !== null;

        if (selectedFileId !== null) {
            input.value = '';
        }

        deleteBtn.disabled = !selectedDirectoryId && !selectedFileId;
        downloadBtn.disabled = !selectedFileId;
    }

    input.addEventListener('input', uploadButton);

    function getDirectoryPath(directoryId, directories) {
        let path = '';
        let currentDir = directories.find(dir => dir.id == directoryId);

        while (currentDir) {
            path = currentDir.name + (path ? '/' + path : '');
            currentDir = directories.find(dir => dir.id == currentDir.parent_id);
        }

        return path;
    }

    document.querySelector('.sidebar__directories').addEventListener('click', handleDirectoryClick);

    function handleDirectoryClick(e) {
        const clickedDirectory = e.target.closest('.sidebar__directory');
        const clickedFile = e.target.closest('.sidebar__file');

        if (clickedDirectory && !clickedFile) {
            document.querySelectorAll('.sidebar__directory').forEach(dir => dir.classList.remove('selected'));
            clickedDirectory.classList.add('selected');
            document.querySelectorAll('.sidebar__file').forEach(file => file.classList.remove('selected'));

            selectedDirectoryId = clickedDirectory.dataset.id;
            selectedFileId = null;

            const directoriesUl = clickedDirectory.closest('ul');
            const allDirectoriesData = directoriesUl && directoriesUl.dataset.directories
                ? JSON.parse(directoriesUl.dataset.directories)
                : [];

            const selectedPath = getDirectoryPath(selectedDirectoryId, allDirectoriesData);
            selectedPathText.textContent = `Выбрано: ${selectedPath}`;

            previewImage.src = '/images/no-photo.png';
            uploadButton();
        }

        if (clickedFile) {
            document.querySelectorAll('.sidebar__file').forEach(file => file.classList.remove('selected'));
            clickedFile.classList.add('selected');
            document.querySelectorAll('.sidebar__directory').forEach(dir => dir.classList.remove('selected'));

            const fileName = clickedFile.textContent;
            const directoriesUl = clickedFile.closest('ul').previousElementSibling;
            const allDirectoriesData = directoriesUl && directoriesUl.dataset.directories
                ? JSON.parse(directoriesUl.dataset.directories)
                : [];

            const parentDirectory = clickedFile.closest('.sidebar__directory');
            if (parentDirectory) {
                selectedDirectoryId = parentDirectory.dataset.id;
            }

            const selectedPath = getDirectoryPath(selectedDirectoryId, allDirectoriesData);
            selectedPathText.textContent = `Выбрано: ${selectedPath}/${fileName}`;

            selectedFileId = clickedFile.dataset.id;

            const filePath = `/uploads/show?file=${encodeURIComponent(fileName)}`;
            if (fileName.match(/\.(jpg|jpeg|png|gif)$/)) {
                previewImage.src = filePath;
                previewImage.classList.add('img');
            } else {
                previewImage.src = '/images/no-photo.png';
                previewImage.classList.add('no-img');
            }

            uploadButton();
        }
    }

    function renderDirectoryTree(directories, files, parentId = null) {
        let html = `<ul data-directories='${JSON.stringify(directories)}'>`;
        directories.forEach(directory => {
            if (directory.parent_id === parentId) {
                html += `<li class="sidebar__directory" data-id="${directory.id}">${directory.name}`;
                html += renderDirectoryTree(directories, files, directory.id);
                let filesHtml = '';
                files.forEach(file => {
                    if (file.directory_id === directory.id) {
                        filesHtml += `<li class="sidebar__file" data-id="${file.id}">${file.name}</li>`;
                    }
                });
                if (filesHtml) {
                    html += `<ul>${filesHtml}</ul>`;
                }
                html += '</li>';
            }
        });
        html += '</ul>';
        return html;
    }

    function updateDirectoryTree(data) {
        const treeHtml = renderDirectoryTree(data.directories, data.files);
        const sidebarDirectories = document.querySelector('.sidebar__directories');
        sidebarDirectories.innerHTML = treeHtml;

        sidebarDirectories.removeEventListener('click', handleDirectoryClick);
        sidebarDirectories.addEventListener('click', handleDirectoryClick);

        uploadButton();
    }

    folderBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const folderName = input.value;

        fetch('/api/add-folder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ folderName, parentId: selectedDirectoryId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateDirectoryTree(data);
                    input.value = '';
                    selectedDirectoryId = null;
                    selectedFileId = null;
                    selectedPathText.textContent = 'Выбрано: ';
                    previewImage.src = '/images/no-photo.png';
                    errorSpan.style.display = 'none';
                    uploadButton();
                } else {
                    errorSpan.style.display = 'block';
                    input.value = '';
                }
            })
            .catch(error => console.error('Error:', error));
    });

    fileBtn.addEventListener('click', function (e) {
        e.preventDefault();

        if (!selectedDirectoryId) {
            alert("Сначала выберите директорию.");
            return;
        }

        const fileInput = document.createElement('input');
        fileInput.type = 'file';

        fileInput.onchange = function () {
            const file = fileInput.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('parentId', selectedDirectoryId);

                fetch('/api/upload-file', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('File upload failed');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            fetch('/api/get-directories')
                                .then(response => response.json())
                                .then(data => {
                                    errorUploadFile.style.display = 'none';
                                    updateDirectoryTree(data);
                                });
                        } else {
                            throw new Error(data.error || 'Unknown error');
                        }
                    })
                    .catch(error => {
                        errorUploadFile.style.display = 'none';

                        if (file.size > 20 * 1024 * 1024) { 
                            errorUploadFile.textContent = "Размер файла не должен превышать 20 МБ";
                        } else if (!file.name.match(/\.(jpg|jpeg|png|gif)$/)) { 
                            errorUploadFile.textContent = "Недопустимый тип файла";
                        } else {
                            errorUploadFile.textContent = "Ошибка загрузки файла";
                        }
                        errorUploadFile.style.display = 'block';
                    });
            }
        };

        fileInput.click();
    });

    deleteBtn.addEventListener('click', function (e) {
        e.preventDefault();

        if (!selectedFileId && !selectedDirectoryId) {
            alert("Сначала выберите файл или директорию.");
            return;
        }

        const isFile = document.querySelector('.sidebar__file.selected') ? true : false;
        const deleteUrl = isFile ? '/api/delete-file' : '/api/delete-folder';

        const idToDelete = isFile ? selectedFileId : selectedDirectoryId;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idToDelete })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetch('/api/get-directories')
                        .then(response => response.json())
                        .then(data => {
                            updateDirectoryTree(data);
                            selectedDirectoryId = null;
                            selectedFileId = null;
                            selectedPathText.textContent = 'Выбрано: ';
                            previewImage.src = '/images/no-photo.png';
                            uploadButton();
                        });
                } else {
                    alert(data.error);
                }
            })
            .catch(error => console.error('Error:', error));
    });

    downloadBtn.addEventListener('click', function (e) {
        e.preventDefault();

        if (!selectedFileId) {
            alert("Сначала выберите файл для скачивания.");
            return;
        }

        const downloadUrl = `/api/download-file?id=${selectedFileId}`;

        const a = document.createElement('a');
        a.href = downloadUrl;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    fetch('/api/get-directories')
        .then(response => response.json())
        .then(data => {
            updateDirectoryTree(data);
        })
        .catch(error => console.error('Error fetching directories:', error));

    uploadButton();
});
