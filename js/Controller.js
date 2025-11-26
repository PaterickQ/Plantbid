class Controller {
    #model;
    #view;
    
    constructor(model, view) {
        this.#model = model;
        this.#view = view;

        this.#view.fillWrapperByArr(this.#model.getStones());
    }

    
}