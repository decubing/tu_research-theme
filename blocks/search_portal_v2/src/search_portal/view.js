import { render } from "@wordpress/element";

import { BrowserRouter as Router } from "react-router-dom";
import ProjectFilter from "./components/ProjectFilter";

export default function View() {
	return (
		<Router>
			<ProjectFilter />
		</Router>
	);
}

render(
	<React.StrictMode>
		<View />
	</React.StrictMode>,
	document.getElementById("search_portal_v2_block")  
);
