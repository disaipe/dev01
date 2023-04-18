import { h } from 'vue';

/**
 * Functional component to use render function in templates
 */
const Render = (props) => {
    const { render } = props;

    return render(h);
};

Object.defineProperties(Render, {
    name: {
        value: 'Render',
        writable: false
    },
    props: {
        value: {
            render: Function
        }
    }
});

export default Render;
