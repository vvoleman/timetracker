document.querySelector('.user__image').addEventListener("click", dropdown);

function dropdown() {
    document.querySelector('.user__dropdown').classList.toggle('user__dropdown--visible')
}