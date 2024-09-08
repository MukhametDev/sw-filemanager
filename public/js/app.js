// API объект для взаимодействия с сервером
const api = {
    getDirectories: () => fetch('/api/get-directories').then(res => res.json()),
    addFolder: (folderName, parentId) =>
        fetch('/api/add-folder', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ folderName, parentId }),
        }).then(res => res.json()),
    uploadFile: (formData) =>
        fetch('/api/upload-file', {
            method: 'POST',
            body: formData,
        }).then(res => res.json()),
    deleteItem: (id, isFile) => {
        const url = isFile ? '/api/delete-file' : '/api/delete-folder';
        return fetch(url, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        }).then(res => res.json());
    },
};

// Глобальный объект для хранения состояния и элементов интерфейса
const appState = {
    selectedDirectoryId: null,
    selectedFileId: null,
    elements: {},
};

// Получение пути директории
function getDirectoryPath(directoryId, directories) {
    let path = '';
    let currentDir = directories.find(dir => dir.id == directoryId);

    while (currentDir) {
        path = currentDir.name + (path ? '/' + path : '');
        currentDir = directories.find(dir => dir.id == currentDir.parent_id);
    }

    return path;
}

// Очистка пути
function clearPath() {
    const { selectedPathText } = appState.elements;
    selectedPathText.textContent = '';
}

// Функция обновления состояния кнопок
function uploadButton() {
    const { input, folderBtn, fileBtn, deleteBtn, downloadBtn } = appState.elements;
    const isInputEmpty = !input.value.trim();

    folderBtn.disabled = isInputEmpty || appState.selectedFileId !== null;
    fileBtn.disabled = !appState.selectedDirectoryId || appState.selectedFileId !== null;
    input.disabled = appState.selectedFileId !== null;

    if (appState.selectedFileId !== null) {
        input.value = '';
    }

    deleteBtn.disabled = !appState.selectedDirectoryId && !appState.selectedFileId;
    downloadBtn.disabled = !appState.selectedFileId;
}

// Функция для рендеринга дерева директорий
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

// Обновление дерева директорий
function updateDirectoryTree(data) {
    const { sidebarDirectories } = appState.elements;
    const treeHtml = renderDirectoryTree(data.directories, data.files);
    sidebarDirectories.innerHTML = treeHtml;

    sidebarDirectories.removeEventListener('click', handleDirectoryClick);
    sidebarDirectories.addEventListener('click', handleDirectoryClick);

    uploadButton();
}

// Обработка кликов по директориям и файлам
function handleDirectoryClick(e) {
    const { selectedPathText, previewImage } = appState.elements;
    const clickedDirectory = e.target.closest('.sidebar__directory');
    const clickedFile = e.target.closest('.sidebar__file');

    document.querySelectorAll('.sidebar__directory, .sidebar__file').forEach(item => item.classList.remove('selected'));

    if (clickedDirectory && !clickedFile) {
        clickedDirectory.classList.add('selected');
        appState.selectedDirectoryId = clickedDirectory.dataset.id;
        appState.selectedFileId = null;

        const directoriesUl = clickedDirectory.closest('ul');
        const allDirectoriesData = directoriesUl?.dataset.directories ? JSON.parse(directoriesUl.dataset.directories) : [];

        const selectedPath = getDirectoryPath(appState.selectedDirectoryId, allDirectoriesData);
        selectedPathText.textContent = selectedPath;

        previewImage.src = '/images/no-photo.png';
        uploadButton();
    }

    if (clickedFile) {
        clickedFile.classList.add('selected');
        const fileName = clickedFile.textContent;
        const fileId = clickedFile.dataset.id;
        const directoriesUl = clickedFile.closest('ul').previousElementSibling;
        const allDirectoriesData = directoriesUl?.dataset.directories ? JSON.parse(directoriesUl.dataset.directories) : [];

        const parentDirectory = clickedFile.closest('.sidebar__directory');
        if (parentDirectory) {
            appState.selectedDirectoryId = parentDirectory.dataset.id;
        }

        const selectedPath = getDirectoryPath(appState.selectedDirectoryId, allDirectoriesData);
        selectedPathText.textContent = `${selectedPath}/${fileName}`;

        appState.selectedFileId = fileId;

        const filePath = `/uploads/show?id=${encodeURIComponent(fileId)}`;
        if (fileName.match(/\.(jpg|jpeg|png|gif)$/)) {
            previewImage.src = filePath;
            previewImage.classList.add('img');
            previewImage.classList.remove('no-img');
        } else {
            previewImage.src = '/images/no-photo.png';
            previewImage.classList.add('no-img');
            previewImage.classList.remove('img');
        }

        uploadButton();
    }
}

// Функция для инициализации событий
function initEventListeners() {
    const { folderBtn, fileBtn, deleteBtn, downloadBtn } = appState.elements;

    folderBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const folderName = appState.elements.input.value;

        api.addFolder(folderName, appState.selectedDirectoryId)
            .then(data => {
                if (data.directories && data.files) {
                    updateDirectoryTree(data);
                    appState.elements.input.value = '';
                    appState.selectedDirectoryId = null;
                    appState.selectedFileId = null;
                    clearPath();
                    appState.elements.previewImage.src = '/images/no-photo.png';
                    appState.elements.errorSpan.style.display = 'none';
                    uploadButton();
                } else {
                    appState.elements.errorSpan.style.display = 'block';
                    appState.elements.input.value = '';
                }
            })
            .catch(error => console.error('Error:', error));
    });

    fileBtn.addEventListener('click', (e) => {
        e.preventDefault();

        if (!appState.selectedDirectoryId) {
            alert('Сначала выберите директорию.');
            return;
        }

        const fileInput = document.createElement('input');
        fileInput.type = 'file';

        fileInput.onchange = function () {
            const file = fileInput.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('file', file);
                formData.append('parentId', appState.selectedDirectoryId);

                api.uploadFile(formData)
                    .then(data => {
                        if (data.directories && data.files) {
                            appState.elements.errorUploadFile.style.display = 'none';
                            updateDirectoryTree(data);
                        } else {
                            throw new Error(data.error || 'Неизвестная ошибка');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка при загрузке файла:', error);
                        appState.elements.errorUploadFile.style.display = 'block';

                        if (file.size > 20 * 1024 * 1024) {
                            appState.elements.errorUploadFile.textContent = 'Размер файла не должен превышать 20 МБ';
                        } else if (!file.name.match(/\.(jpg|jpeg|png|gif)$/)) {
                            appState.elements.errorUploadFile.textContent = 'Недопустимый тип файла';
                        } else {
                            appState.elements.errorUploadFile.textContent = error.message || 'Ошибка загрузки файла';
                        }
                    });
            }
        };

        fileInput.click();
    });

    deleteBtn.addEventListener('click', (e) => {
        e.preventDefault();

        if (!appState.selectedFileId && !appState.selectedDirectoryId) {
            alert('Сначала выберите файл или директорию.');
            return;
        }

        const isFile = !!document.querySelector('.sidebar__file.selected');
        const idToDelete = isFile ? appState.selectedFileId : appState.selectedDirectoryId;

        api.deleteItem(idToDelete, isFile)
            .then(data => {
                if (data.directories && data.files) {
                    updateDirectoryTree(data);
                    appState.selectedDirectoryId = null;
                    appState.selectedFileId = null;
                    clearPath(); // Очистка пути после удаления
                    appState.elements.previewImage.src = '/images/no-photo.png';
                    uploadButton();
                } else if (data.error) {
                    alert(data.error);
                } else {
                    alert('Неизвестная ошибка, данные не содержат необходимых ключей.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при обработке запроса.');
            });
    });

    downloadBtn.addEventListener('click', (e) => {
        e.preventDefault();

        if (!appState.selectedFileId) {
            alert('Сначала выберите файл для скачивания.');
            return;
        }

        const downloadUrl = `/api/download-file?id=${appState.selectedFileId}`;
        const a = document.createElement('a');
        a.href = downloadUrl;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    appState.elements.input.addEventListener('input', uploadButton);
}

// Инициализация приложения
function initApp() {
    // Кэшируем все элементы
    appState.elements = {
        input: document.querySelector('.sidebar__input'),
        folderBtn: document.querySelector('.sidebar__btn:nth-child(1)'),
        fileBtn: document.querySelector('.sidebar__btn:nth-child(2)'),
        deleteBtn: document.querySelector('.sidebar__btn:nth-child(4)'),
        downloadBtn: document.querySelector('.content__btn'),
        selectedPathText: document.querySelector('.content__title'),
        previewImage: document.querySelector('.content__bottom img'),
        errorSpan: document.querySelector('.sidebar__error'),
        errorUploadFile: document.querySelector('.sidebar__error-file'),
        sidebarDirectories: document.querySelector('.sidebar__directories'),
    };

    // Получаем данные и инициализируем дерево директорий
    api.getDirectories()
        .then(data => updateDirectoryTree(data))
        .catch(error => console.error('Error fetching directories:', error));

    uploadButton();
    initEventListeners();
}

// Запуск приложения после загрузки DOM
window.addEventListener('DOMContentLoaded', initApp);
