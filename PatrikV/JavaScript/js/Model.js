class Model {
    
    #size;
    /**
     * Pole kamenů
     * 0 znamená prázdné pole
     * [1,2,3,0]
     */
    #stones = [];
    
    constructor(size) {
        this.#size = size;        
        for(let i = 1; i<size*size; i++) {
            this.#stones.push(""+i);
        }
        this.#stones.push(""+0);        
    }

    getStones(){
        return this.#stones;
    }
}
