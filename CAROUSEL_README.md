# Система картинок книг с каруселью

Полная система управления картинками для книг с поддержкой карусели.

## 📦 Компоненты

### Backend

#### Модель `BookImage`
- `file_path` - путь к файлу картинки
- `sort_order` - порядок сортировки картинок
- `is_primary` - основная картинка (первая по умолчанию)
- `alt_text` - альтернативный текст

#### Контроллер `BookImageController` (`app/Http/Controllers/Api/V1/Admin/`)

Методы API:
- `POST /api/v1/admin/books/{book}/images` - загрузить картинку
- `PATCH /api/v1/admin/books/{book}/images/{image}` - обновить метаданные
- `DELETE /api/v1/admin/books/{book}/images/{image}` - удалить картинку
- `POST /api/v1/admin/books/{book}/images/reorder` - переупорядочить картинки

### Frontend

#### Файл `carousel.js`
Основной компонент с двумя вариантами:

1. **Полная карусель (Splide.js)**
   ```javascript
   import { initBookCarousel } from './carousel.js';
   
   const images = [
     { url: '/storage/books/image1.jpg', alt_text: 'Cover' },
     { url: '/storage/books/image2.jpg', alt_text: 'Page 1' }
   ];
   
   initBookCarousel(container, images);
   ```

2. **Простая галерея с миниатюрами**
   ```javascript
   import { initSimpleImageGallery } from './carousel.js';
   
   initSimpleImageGallery(container, images);
   ```

#### Файл `carousel-api.js`
Функции для работы с API:

```javascript
// Загрузить книгу с картинками
await loadBookWithCarousel(bookId, 'carousel-container');

// Загрузить картинку
await uploadBookImage(bookId, file, 'alt text');

// Удалить картинку
await deleteBookImage(bookId, imageId);

// Обновить метаданные
await updateBookImage(bookId, imageId, { is_primary: true });

// Переупорядочить
await reorderBookImages(bookId, [id1, id2, id3]);
```

## 🔧 Установка и миграция

### 1. Выполнить миграцию БД
```bash
php artisan migrate
```

### 2. Установить зависимости npm
```bash
npm install
```

Это установит `splide@^4.1.1` для карусели.

### 3. Убедиться в настройке хранилища
Файлы хранятся в `storage/app/public/books/`. 

Убедитесь, что создана символическая ссылка:
```bash
php artisan storage:link
```

## 📝 Примеры использования

### Пример 1: Карусель на странице продукта

**HTML (Blade template):**
```blade
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Карусель -->
    <div id="bookCarousel" class="bg-gray-100 rounded-lg overflow-hidden" style="height: 500px;"></div>
    
    <!-- Информация о книге -->
    <div>
        <h1 id="bookTitle" class="text-3xl font-bold mb-4"></h1>
        <p id="bookAuthor" class="text-lg text-gray-600 mb-4"></p>
        <p id="bookPrice" class="text-2xl font-bold text-green-600 mb-4"></p>
        <button class="btn btn-primary" onclick="addToCart()">В корзину</button>
    </div>
</div>

<script type="module">
    import { loadBookWithCarousel } from '{{ asset('js/carousel-api.js') }}';
    
    // Получить ID книги из URL или data-атрибута
    const bookId = {{ $book->id }};
    
    // Загрузить и вывести карусель
    const book = await loadBookWithCarousel(bookId, 'bookCarousel');
    
    // Заполнить информацию о книге
    document.getElementById('bookTitle').textContent = book.title;
    document.getElementById('bookAuthor').textContent = book.author;
    document.getElementById('bookPrice').textContent = `${book.price} ₽`;
</script>
```

### Пример 2: Загрузка картинок в админке

**HTML:**
```html
<form id="imageUploadForm" enctype="multipart/form-data">
    <div class="mb-4">
        <label for="image" class="block text-sm font-medium mb-2">
            Выберите картинку
        </label>
        <input 
            type="file" 
            id="image" 
            name="image" 
            accept="image/*" 
            required
            class="block w-full border border-gray-300 rounded px-3 py-2"
        />
    </div>
    
    <div class="mb-4">
        <label for="altText" class="block text-sm font-medium mb-2">
            Альтернативный текст
        </label>
        <input 
            type="text" 
            id="altText" 
            name="alt_text"
            class="block w-full border border-gray-300 rounded px-3 py-2"
        />
    </div>
    
    <button type="submit" class="btn btn-primary">
        Загрузить
    </button>
</form>

<div id="imagesList" class="mt-8">
    <!-- Будут отображены загруженные картинки -->
</div>

<script type="module">
    import { uploadBookImage } from '{{ asset('js/carousel-api.js') }}';
    
    const bookId = {{ $book->id }};
    const form = document.getElementById('imageUploadForm');
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const file = document.getElementById('image').files[0];
        const altText = document.getElementById('altText').value;
        
        try {
            const image = await uploadBookImage(bookId, file, altText);
            console.log('Картинка загружена:', image);
            form.reset();
            // Обновить список картинок...
        } catch (error) {
            alert('Ошибка загрузки: ' + error.message);
        }
    });
</script>
```

### Пример 3: Простая галерея без Splide

Если вы хотите простую галерею без полного функционала карусели:

```javascript
import { initSimpleImageGallery } from './carousel.js';

// Вместо:
// initBookCarousel(container, images);

// Используйте:
initSimpleImageGallery(container, images);
```

## 🎨 Стилизация карусели

### CSS классы Splide, которые можно переопределить:

```css
/* Контейнер карусели */
.splide {
    /* ваши стили */
}

/* Кнопки навигации */
.splide__arrow {
    background: rgba(0, 0, 0, 0.5);
    color: white;
}

.splide__arrow:hover {
    background: rgba(0, 0, 0, 0.8);
}

/* Пагинация (точки) */
.splide__pagination__page {
    background: rgba(0, 0, 0, 0.4);
}

.splide__pagination__page.is-active {
    background: rgba(0, 0, 0, 0.9);
}

/* Прогресс-бар */
.splide__progress__bar {
    background: linear-gradient(to right, #3b82f6, #1e40af);
}
```

## 📊 Структура API ответов

### Получить книгу с картинками
```
GET /api/v1/books/{id}
```

Ответ:
```json
{
  "success": true,
  "message": "Книга",
  "data": {
    "book": {
      "id": 1,
      "title": "Война и мир",
      "author": "Лев Толстой",
      "price": "499.99",
      "images": [
        {
          "id": 1,
          "url": "http://localhost/storage/books/image1.jpg",
          "alt_text": "Обложка",
          "is_primary": true,
          "sort_order": 0
        },
        {
          "id": 2,
          "url": "http://localhost/storage/books/image2.jpg",
          "alt_text": "Разворот 1",
          "is_primary": false,
          "sort_order": 1
        }
      ]
    }
  }
}
```

### Загрузить картинку
```
POST /api/v1/admin/books/{book}/images
Content-Type: multipart/form-data

image: <file>
alt_text: "Обложка"
```

Ответ:
```json
{
  "success": true,
  "message": "Изображение загружено",
  "data": {
    "image": {
      "id": 3,
      "url": "http://localhost/storage/books/1740158400_abc123.jpg",
      "alt_text": "Обложка",
      "is_primary": false,
      "sort_order": 2
    }
  }
}
```

## ⚙️ Опции карусели

При инициализации можно передать опции:

```javascript
initBookCarousel(container, images, {
    type: 'fade',           // 'fade' или 'slide'
    perPage: 1,             // Картинок на странице
    rewind: true,           // Зацикливание
    autoplay: 3000,         // Автопроигрывание (ms) или false
    lazyLoad: 'sequential', // Ленивая загрузка
    arrows: true,           // Кнопки навигации
    pagination: true,       // Точки навигации
    speed: 400,             // Скорость анимации
});
```

## 🐛 Решение проблем

### Картинки не загружаются
1. Проверьте, что `php artisan storage:link` выполнен
2. Проверьте права доступа на папку `storage/app/public/`
3. Проверьте URL в ответе API

### Carousell не инициализируется
1. Убедитесь, что `npm install` выполнен
2. Проверьте, что `resources/js/carousel.js` импортируется
3. Откройте консоль браузера для ошибок

### Картинки не сортируются после переупорядочивания
- Убедитесь, что отправляется полный массив ID в правильном порядке

## 🚀 Дальнейшие улучшения

Возможные расширения:

1. **Оптимизация изображений** - Использовать ImageMagick / Intervention Image
2. **Разные размеры** - Создавать thumbnails, обычные, и полные картинки
3. **Drag-and-drop** - UI для переупорядочивания картинок
4. **Lightbox** - Полноэкранный просмотр при клике на картинку
5. **WebP поддержка** - Автоматическое преобразование формата
