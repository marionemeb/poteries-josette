import React from "react";
import ReactDOM from "react-dom";

export default class Recipes extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isHidden: this.props.isHidden,
            imageBroken: false
        };
        this.openButton = React.createRef();
        this.closeButton = React.createRef();
        this.handleClick = this.handleClick.bind(this);
        this.handleKeyDown = this.handleKeyDown.bind(this);
    }

    componentDidUpdate(prevProps, prevState) {
        // Keyboard focus follows the pop-up: into it on open, back on close.
        if (prevState.isHidden && !this.state.isHidden && this.closeButton.current) {
            this.closeButton.current.focus();
        } else if (!prevState.isHidden && this.state.isHidden && this.openButton.current) {
            this.openButton.current.focus();
        }
    }

    componentWillUnmount() {
        document.removeEventListener('keydown', this.handleKeyDown);
    }

    handleClick() {
        const isHidden = !this.state.isHidden;
        this.setState({isHidden: isHidden});
        // The page behind the popup must not scroll while it is open.
        document.body.classList.toggle('popup-open', !isHidden);
        if (isHidden) {
            document.removeEventListener('keydown', this.handleKeyDown);
        } else {
            document.addEventListener('keydown', this.handleKeyDown);
        }
    };

    handleKeyDown(event) {
        if (event.key === 'Escape') {
            this.handleClick();
        }
    }

    render() {
        const {name, description, ingredient, imageName, category, pdfUrl} = this.props;

        return (
            <React.Fragment>
                <button type="button" className="btn-pill" ref={this.openButton} onClick={this.handleClick}>
                    Voir la recette
                </button>
                {/* Rendered at the end of <body>: inside the recipe card,
                    position: fixed would be confined to the card instead of the screen. */}
                {!this.state.isHidden && ReactDOM.createPortal(
                    <div className="recipes-visibility" onClick={this.handleClick}>
                        {/* Clicks inside the card must not reach the backdrop, which closes it. */}
                        <div className="recipe-card" role="dialog" aria-modal="true" aria-label={name}
                             onClick={e => e.stopPropagation()}>
                            <button type="button" className="recipe-card-close" aria-label="Fermer"
                                    ref={this.closeButton} onClick={this.handleClick}>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     strokeWidth="2" strokeLinecap="round" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6L6 18"/>
                                </svg>
                            </button>
                            {imageName && !this.state.imageBroken &&
                                <img className="recipe-card-photo" src={'/uploads/images/products/' + imageName}
                                     alt="" onError={() => this.setState({imageBroken: true})}/>
                            }
                            <article className="recipe-card-body">
                                {category && <span className="tile-card-label">{category}</span>}
                                <h2>{name}</h2>
                                {ingredient &&
                                    <div className="recipe-card-ingredients">
                                        <strong>Ingrédients</strong>
                                        <p>{ingredient}</p>
                                    </div>
                                }
                                {description && <p className="recipe-card-steps">{description}</p>}
                                {pdfUrl &&
                                    <a className="btn-pill btn-pill-ghost" href={pdfUrl} target="_blank" rel="noopener">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                                            <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                            <rect x="6" y="14" width="12" height="7"/>
                                        </svg>
                                        Imprimer la recette
                                    </a>
                                }
                            </article>
                        </div>
                    </div>,
                    document.body
                )}
            </React.Fragment>
        );
    }
}
