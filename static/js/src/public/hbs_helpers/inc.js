module.exports = function (value, operator) {
    switch (operator) {
        case '-':
            return parseInt(value, 10) - 1;
        case '+':
            return parseInt(value, 10) + 1;
        default:
            return parseInt(value, 10);
    }
};
