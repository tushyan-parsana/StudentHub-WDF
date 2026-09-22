// Select all FAQ questions
const faqQuestions = document.querySelectorAll(".faq-question");


// Add click event to every FAQ question
faqQuestions.forEach(function(question){

    question.addEventListener("click", function(){

        // Get the parent FAQ item
        const faqItem = this.parentElement;

        // Get the answer
        const answer = faqItem.querySelector(".faq-answer");

        // Get the + / - icon
        const icon = faqItem.querySelector(".faq-icon");


        // Toggle the active class
        faqItem.classList.toggle("active");


        // Check whether FAQ is open
        if(faqItem.classList.contains("active")){

            answer.style.display = "block";
            icon.textContent = "−";

        }else{

            answer.style.display = "none";
            icon.textContent = "+";

        }

    });

});