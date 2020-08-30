import React from "react";

export default class Recipes extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isHidden: this.props.isHidden,
            name: this.props.name,
            description: this.props.description,
            ingredient: this.props.ingredient,
            imageName: this.props.imageName
        };
        this.handleClick = this.handleClick.bind(this)
    }

    handleClick() {
        const isHidden = this.state.isHidden;
        this.setState({isHidden: !isHidden});
    };

    render() {
        console.log(this.state.isHidden)
        return (
            <div>
                <i type="button"
                   className="fas fa-eye"
                   onClick={this.handleClick}
                ></i>
                <div className={this.state.isHidden ? "recipes-visibility hidden" : "recipes-visibility"}
                     onClick={this.handleClick}>
                    <div className="popup recipe-card">
                        <a className="close" onClick={this.handleClick}>&#10008;</a>
                        <aside>
                            <img src={'/uploads/images/products/' + this.state.imageName}
                                 alt={this.state.name}/>
                        </aside>
                        <article>
                            <h2>{this.state.name}</h2>
                            <p className="ingredients"><span>Ingredients:&nbsp;</span>{this.state.ingredient}</p>
                            <p>{this.state.description}</p>
                        </article>
                    </div>
                </div>
            </div>
        );
    }
}
