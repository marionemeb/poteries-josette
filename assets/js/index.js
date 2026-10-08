//REACT
import React from "react";
import { createRoot } from "react-dom/client";
import Recipes from "./components/Recipes";

document.querySelectorAll("div.react").forEach(function (div) {
    createRoot(div).render(<Recipes
        isHidden={true}
        name={div.dataset.name}
        description={div.dataset.description}
        ingredient={div.dataset.ingredient}
        imageName={div.dataset.imageName}
        category={div.dataset.category}
        pdfUrl={div.dataset.pdfUrl}
    />);
});
