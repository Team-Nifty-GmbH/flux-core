/**
 * I clone and render the given source template.
 */
export default function(element, metadata, framework) {
    // Get the template reference that we want to clone and render.
    let templateRef = framework.evaluate(metadata.expression);
    // Clone the template and get the root node - this is the node that we will
    // inject into the DOM.
    const clone = templateRef.content
        .cloneNode(true)
        .firstElementChild;
    // CAUTION: The following logic ASSUMES that the template-outlet directive has
    // an "x-data" scope binding on it. If it didn't we would have to change the
    // logic. But, I don't think Alpine.js has mechanics to solve this use-case
    // quite yet.
    Alpine.addScopeToNode(
        clone,
        // Use the "x-data" scope from the template-outlet element as a means to
        // supply initializing data to the clone (for constructor injection).
        Alpine.closestDataStack(element)[0],
        // use the template-outlet element's parent to define the rest of the
        // scope chain.
        element.parentElement
    );
    // Instead of leaving the template in the DOM, we're going to swap the
    // template with a comment hook. This isn't necessary; but, I think it leaves
    // the DOM more pleasant looking.
    let domHook = document.createComment(` Template outlet hook (${metadata.expression}) with bindings (${element.getAttribute("x-data")}). `);
    domHook._template_outlet_ref = templateRef;
    domHook._template_outlet_clone = clone;
    // Swap the template-outlet element with the hook and clone.
    // --
    // NOTE: Doing this inside the mutateDom() method will pause Alpine's internal
    // MutationObserver, which allows us to perform DOM manipulation without
    // triggering actions in the framework. Then, we can call initTree() and
    // destroyTree() to have explicitly setup and teardowm DOM node bindings.
    Alpine.mutateDom(
        function pauseMutationObserver() {
            element.after(domHook);
            domHook.after(clone);
            Alpine.initTree(clone);
            element.remove();
            Alpine.destroyTree(element);
        }
    );
}
