document.addEventListener("DOMContentLoaded", () => {
    const imageInput = document.querySelector('input[type="file"]');
    const imagePreview = document.getElementById("imagePreview");
    const dynamicFields = document.getElementById("dynamicFields");
    const keywordsWrapper = document.getElementById("keywordsWrapper");
    const keywordInput = document.getElementById("keywordInput");
    const keywordsList = document.getElementById("keywordsList");

    let keywords = [];

    // --- Превью и загрузка ключевых слов через OCR ---
    imageInput.addEventListener("change", (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                // Показываем превью
                imagePreview.src = e.target.result;
                imagePreview.classList.remove("hidden");

                // Показываем скрытые поля
                dynamicFields.classList.remove("hidden");
                keywordsWrapper.classList.remove("hidden");

                // Отправка на сервер для распознавания текста
                const formData = new FormData();
                formData.append('imageFile', file);

                fetch('/upload/preview', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.keywords) {
                            keywords = data.keywords;
                            renderKeywords();
                        }
                    })
                    .catch(err => console.error('Error fetching OCR preview:', err));
            };
            reader.readAsDataURL(file);
        }
    });

    // --- Добавление ключевых слов вручную ---
    keywordInput.addEventListener("keypress", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            const value = keywordInput.value.trim();
            if (value && !keywords.includes(value)) {
                keywords.push(value);
                renderKeywords();
            }
            keywordInput.value = "";
        }
    });

    // --- Отображение ключевых слов ---
    function renderKeywords() {
        keywordsList.innerHTML = "";
        keywords.forEach((word, index) => {
            const chip = document.createElement("div");
            chip.classList.add("keyword-chip");
            chip.innerHTML = `${word} <span data-index="${index}">×</span>`;

            // Кликабельное слово (можно добавить поиск)
            chip.addEventListener("click", (e) => {
                if(e.target.tagName !== "SPAN"){
                    alert(`Вы кликнули по ключевому слову: ${word}`);
                }
            });

            keywordsList.appendChild(chip);
        });
    }

    // --- Удаление слова по крестику ---
    keywordsList.addEventListener("click", (event) => {
        if (event.target.tagName === "SPAN") {
            const index = event.target.getAttribute("data-index");
            keywords.splice(index, 1);
            renderKeywords();
        }
    });

    // --- Перед отправкой формы передаём ключевые слова ---
    const uploadForm = document.getElementById("uploadForm");
    uploadForm.addEventListener("submit", (e) => {
        // Создаём скрытое поле keywords
        let hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = "keywords";
        hiddenInput.value = keywords.join(",");
        uploadForm.appendChild(hiddenInput);
    });
});
