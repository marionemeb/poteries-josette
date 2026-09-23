import React from "react";
import ReactDOM from "react-dom";

export default class Recipes extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isHidden: this.props.isHidden,
            imageBroken: false,
            name: this.props.name,
            description: this.props.description,
            ingredient: this.props.ingredient,
            imageName: this.props.imageName
        };
        this.handleClick = this.handleClick.bind(this);
        this.handleKeyDown = this.handleKeyDown.bind(this);
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
        return (
            <div>
                <i type="button"
                   className="fas fa-eye"
                   onClick={this.handleClick}
                ></i>
                {/* Rendered at the end of <body>: inside the recipe card (CSS columns),
                    position: fixed would be confined to the card instead of the screen. */}
                {!this.state.isHidden && ReactDOM.createPortal(
                    <div className="recipes-visibility" onClick={this.handleClick}>
                        {/* Clicks inside the card must not reach the backdrop, which closes it. */}
                        <div className="popup recipe-card" onClick={e => e.stopPropagation()}>
                            <button type="button" className="close" aria-label="Fermer" onClick={this.handleClick}>
                                &times;
                            </button>
                            {this.state.imageName && !this.state.imageBroken &&
                                <aside>
                                    <img src={'/uploads/images/products/' + this.state.imageName}
                                         alt={this.state.name}
                                         onError={() => this.setState({imageBroken: true})}/>
                                </aside>
                            }
                            <article>
                                <h2>{this.state.name}</h2>
                                <p>{this.state.description}</p>
                                <p className="ingredients"><span>Ingrédients :&nbsp;</span>{this.state.ingredient}</p>
                            </article>
                        </div>
                    </div>,
                    document.body
                )}
            </div>
        );
    }
}
