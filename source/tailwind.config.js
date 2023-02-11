module.exports = {
    content: [
        './resources/js/**/*.vue'
    ],
    safelist: [
        // widths
        { pattern: /^w-/ },

        // grid gaps
        { pattern: /^gap-/ }
    ]
};
