class View {
    #wrapper;
    constructor(id) {
        this.#wrapper = document.getElementById(id);
    }

    fillWrapperByArr(stones) {
        for(let i of stones) {
            let el = document.createElement('div');
            el.dataset.id = i;
            el.innerHTML = i;
            el.classList.add("stone");
            this.#wrapper.appendChild(el);
            if(i == 0){
                el.classList.add("empty");
                el.innerHTML = "";
            }
        }
    }
}

