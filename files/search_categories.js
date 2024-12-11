async function searchCategories(query) {
    const response = await fetch(`categories_list.php?name=${query}`);
    const categories = await response.json();
    const resultsDiv = document.getElementById('category-results');
    resultsDiv.innerHTML = '';

    categories.forEach(category => {
        const categoryDiv = document.createElement('div');
        categoryDiv.classList.add('cursor-pointer', 'p-2', 'hover:bg-gray-200');
        categoryDiv.textContent = category.name;
        categoryDiv.addEventListener('click', () => {
            document.getElementById('category_id').value = category.id;
            document.getElementById('category_name').value = category.name;
            resultsDiv.innerHTML = '';
        });
        resultsDiv.appendChild(categoryDiv);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('category_name').addEventListener('input', (event) => {
        searchCategories(event.target.value);
    });
});
