//REACT
import React from "react";
import ReactDOM from "react-dom";
import Recipes from "./components/Recipes";
import "../scss/app.scss";

document.querySelectorAll("div.react").forEach(function (div) {
    const isHidden = true;
    const name = div.dataset.name;
    const description = div.dataset.description;
    const ingredient = div.dataset.ingredient;
    const imageName = div.dataset.imageName;

    ReactDOM.render(<Recipes
        isHidden={isHidden}
        name={name}
        description={description}
        ingredient={ingredient}
        imageName={imageName}
    />, div);
});

