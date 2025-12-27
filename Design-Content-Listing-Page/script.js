function toggle(button) {
    const fullText = button.previousElementSibling;

    if (fullText.style.display === "block") {
        fullText.style.display = "none";
        button.innerText = "Show More";
    } else {
        fullText.style.display = "block";
        button.innerText = "Show Less";
    }
}