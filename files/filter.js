var categoriesTree = []

function loadProducts() {
    let params = new URLSearchParams(new FormData(document.getElementById('filterForm'))).toString();
    fetch('items_list.php?' + params)
        .then(response => response.text())
        .then(html => {
            document.getElementById('products-container').innerHTML = html;
        });
}

function loadCategories(parentId = null, goingBack = false) {
    let params = parentId ? 'parent_id=' + parentId : '';
    fetch('categories_list.php?' + params)
        .then(response => response.json())
        .then(categories => {
            if (!goingBack) {
                categoriesTree.push(parentId)
            }

            let categoriesList = document.getElementById('categoriesList');
            categoriesList.innerHTML = '';

            if (parentId) {
                document.getElementById('backButton').style.display = 'block';
            } else {
                document.getElementById('backButton').style.display = 'none';
            }

            categories.forEach(category => {
                let listItem = document.createElement('li');
                let link = document.createElement('a');
                link.href = '#';
                link.textContent = `${category.name} (${category.products_count})`;
                link.classList.add('category-link', 'block', 'text-gray-700', 'hover:bg-gray-200', 'p-2', 'rounded');
                link.dataset.categoryId = category.id;
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    parentCategoryId = category.id;
                    document.querySelector('input[name="category_id"]').value = category.id;
                    loadCategories(category.id);
                    loadProducts();
                });
                listItem.appendChild(link);
                categoriesList.appendChild(listItem);
            });
        });
}

document.getElementById('filterForm').addEventListener('submit', function(event) {
    event.preventDefault();
    loadProducts();
});

document.getElementById('search').addEventListener('input', loadProducts);
document.getElementById('min_price').addEventListener('input', loadProducts);
document.getElementById('max_price').addEventListener('input', loadProducts);
document.getElementById('sort').addEventListener('change', loadProducts);

document.getElementById('backButton').addEventListener('click', function() {
    categoriesTree.pop()
    loadCategories(categoriesTree.pop(), true);
    loadProducts();
});

window.addEventListener('load', () => {
    loadCategories();
    loadProducts();
});