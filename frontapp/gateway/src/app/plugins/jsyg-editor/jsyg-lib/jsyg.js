(function(factory) {

    module.exports = factory(
        require("./jsyg.utils"),
        require("./jsyg.events"),
        require("./jsyg.stdconstruct")
    );

}(function(JSYG,Events,StdConstruct) {

    JSYG.Events = Events;
    JSYG.StdConstruct = StdConstruct;

    return JSYG;
}))

