//var textArray = ['Text 1', 'Text 2', 'Text 3', 'Text 4', 'Text 5'];
//var rotatingText = document.getElementById('rotatingText');
//var counter = 0;
// setInterval(changeText, 1000);

// function changeText()
// {
//     rotatingText.innerHTML = textArray[counter];
//     rotatingText.style.animation = 'none';
//     rotatingText.offsetHeight; /* trigger reflow */
//     rotatingText.style.animation = null;
//     counter++;
//     if (counter >= textArray.length)
//     {
//         counter = 0;
//     }
// }

class SlideText
{
    constructor(elementId)
    {
        this.element    = document.querySelector(elementId);
        this.itemArray  = [];
        this.delay      = 1; // in seconds
        this.transition = undefined;
        this.itemIndex  = 0;

        this.element.style.position = 'relative';
        /*
        When we call 'this.slide' inside setInterval, it doesn't have 
        the context of the SlideText instance, so 'this' inside slide 
        doesn't refer to the SlideText instance as we might expect.
        To fix this, we can bind 'this' to slide in the constructor 
        of SlideText
        */

        // Bind 'this' to the slide method
        this.slide = this.slide.bind(this);
    }

    set items(list) {
        this.itemArray = list;

        // If the currently displayed text is equal to
        // the first item in the item array, move the
        // index to the next item, to prevent the first
        // from showing up again in the next slide
        if (this.itemsLength > 1 && this.element.innerHTML == this.itemArray[0])
            this.itemIndex = 1;
    }

    set slideDelay(nextDelay) {
        this.delay = nextDelay / 1000; // convert ms to s for CSS
    }

    slide()
    {
        this.element.innerHTML = this.itemArray[this.itemIndex];
        this.clearAnimation();

        // Reapply animation
        this.element.style.animation = 'slideup 1.5s ease';

        this.itemIndex++;

        if (this.itemIndex >= this.itemsLength)
            this.itemIndex = 0;
    }

    start() {
        this.transition = setInterval(this.slide, this.delay * 1000); // convert s back to ms for JS
    }

    clearAnimation()
    {
        // Remove the animation
        this.element.style.animation = 'none';

        // Force a reflow
        void this.element.offsetHeight;

        // Reapply animation
        this.element.style.animation = null;
    }

    stop()
    {
        clearInterval(this.transition);
        this.clearAnimation();

        // Set the displayed text to the default
        if (this.itemsLength > 0)
            this.element.innerHTML = this.itemArray[0];
    }

    get itemsLength() {
        return this.itemArray.length;
    }
}
