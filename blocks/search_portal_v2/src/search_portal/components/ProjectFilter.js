import { useState, useEffect, Fragment } from "@wordpress/element";
//import { useBlockProps } from '@wordpress/block-editor';
//import { useSelect } from "@wordpress/data";
import { Listbox, RadioGroup, Transition } from "@headlessui/react";
import { CheckIcon, ChevronUpDownIcon } from "@heroicons/react/20/solid";
import FolderIcon from "@mui/icons-material/Folder";
import apiFetch from "@wordpress/api-fetch";
import { useSearchParams } from "react-router-dom";

export default function ProjectFilter() {
	const [searchParams, setSearchParams] = useSearchParams();

	const qCats = searchParams.get("cat"); //fetch any query strings
	const qTags = searchParams.get("tags");
	const qTopics = searchParams.get("topics");
	const qSchool = searchParams.get("school");
	const qDepartment = searchParams.get("department");
	const undergradOnly = searchParams.get("undergrad_only"); // use this flag to exclude SoM-only projects

	const [categories, setCategories] = useState([]);
	const [tags, setTags] = useState([]);
	const [topics, setTopics] = useState([]);
	const [schools, setSchools] = useState([]);
	const [departments, setDepartments] = useState([]);

	const [selectedCategories, setSelectedCategories] = useState([]);
	const [selectedTags, setSelectedTags] = useState([]);
	const [selectedTopics, setSelectedTopics] = useState([]);
	const [selectedSchools, setSelectedSchools] = useState([]);
	const [selectedDepartments, setSelectedDepartments] = useState([]);

	const [loadingPosts, setLoadingPosts] = useState(false);

	// fetch the cats
	useEffect(() => {
		let thePath;
		if(qSchool !== null){
			//console.log(qSchool);
			console.log("Using SoM Category list...")
			thePath = "tu-research-theme/v1/som-cats";
		}else {
			thePath = "/wp/v2/categories/?per_page=100&post_type=research-listing&_fields=id,count,name,slug";
		} 
		apiFetch({
			//path: "/wp/v2/categories/?per_page=100&post_type=research-listing&_fields=id,count,name,slug",
			path: thePath
		}).then((data) => {
			//console.log(data);
			Object.keys(data).forEach((key) => {
				if (data[key].count === 0) {
					delete data[key];
				}
				if(data && data[key] && data[key].hasOwnProperty("term_id")){
					data[key].id = data[key].term_id;
				} 
			});
			setCategories(data);
			//console.log(data);
			if (qCats !== "") {
				// if we have a query category, set the state from that
				let queryCat = data.filter((obj) => {
					return obj.id == qCats;
				});
				if (queryCat && queryCat.length > 0) {
					setSelectedCategories([queryCat[0]]);
				}
			}
		});
	}, []);

	// fetch the tags
	useEffect(() => {
		/* let thePath;
		if(qSchool !== null){
			console.log("Using SoM Tag list...")
			thePath = "tu-research-theme/v1/som-topics";
		}else {
			thePath = "/wp/v2/tags?per_page=200&post_type=research-listing&_fields=id,count,name,slug";
		}  */
		apiFetch({ path: "tu-research-theme/v1/som-tags" }).then((data) => {
			Object.keys(data).forEach((key) => {
				if (data[key].count === 0) {
					delete data[key];
				}
				if(data && data[key] && data[key].hasOwnProperty("term_id")){
					data[key].id = parseInt(data[key].term_id);
				} 
			});
			setTags(data);
			/* if(qTags !== ""){ // if we have a query tag, set the state from that
				let queryTagsIdArray = qTags.split("|");
				let queryTagsArray = data.filter(obj => {
					return queryTagsIdArray.includes(String(obj.id)) 
				});
				if(queryTagsArray && queryTagsArray.length > 0){
					setSelectedTags(queryTagsArray);
				};
			} */
		});
	}, []); 

	// fetch the topics
	useEffect(() => {
		let thePath;
		if(qSchool !== null){
			console.log("Using SoM Topic list...")
			thePath = "tu-research-theme/v1/som-topics";
		}else {
			thePath = "/wp/v2/topic/?per_page=500&post_type=research-listing&_fields=id,count,name,slug";
		} 
		apiFetch({
			path: thePath,
		}).then((data) => {
			//console.log(data);
			Object.keys(data).forEach((key) => {
				if (data[key].count === 0) {
					delete data[key];
				}
				if(data && data[key] && data[key].hasOwnProperty("term_id")){
					data[key].id = parseInt(data[key].term_id);
				} 
			});
			setTopics(data);
			//console.log(data);
			/* if (qTopics !== "") {
				// if we have a query category, set the state from that
				let queryTopic = data.filter((obj) => {
					return obj.id == qTopics;
				});
				if (queryTopic && queryTopic.length > 0) {
					setSelectedTopics(queryTopic[0]);
				}
			} */
		});
	}, []);

	// fetch the schools
	useEffect(() => {
		apiFetch({
			path: "/wp/v2/school/?per_page=100&post_type=research-listing&_fields=id,count,name,slug",
		}).then((data) => {
			Object.keys(data).forEach((key) => {
				if (data[key].count === 0) {
					delete data[key];
				}
			});
			setSchools(data);
			if (qSchool !== "") {
				// if we have a query category, set the state from that
				let queryTopic = data.filter((obj) => {
					return obj.id == qSchool;
				});
				if (queryTopic && queryTopic.length > 0) {
					setSelectedSchools([queryTopic[0]]);
				}
			}
		});
	}, []);

	// fetch the departments
	useEffect(() => {
		let thePath;
		if(qSchool !== null){
			console.log("Using SoM department list...")
			thePath = "tu-research-theme/v1/som-departments";
		}else {
			thePath = "/wp/v2/department/?per_page=100&post_type=research-listing&_fields=id,count,name,slug";
		} 
		apiFetch({
			path: thePath,
		}).then((data) => {
			Object.keys(data).forEach((key) => {
				if (data[key].count === 0) {
					delete data[key];
				}
				if(data && data[key] && data[key].hasOwnProperty("term_id")){
					data[key].id = data[key].term_id;
				} 
			});
			
			setDepartments(data);
			if (qDepartment !== "") {
				// if we have a query category, set the state from that
				let queryTopic = data.filter((obj) => {
					return obj.id == qDepartment;
				});
				if (queryTopic && queryTopic.length > 0) {
					setSelectedDepartments(queryTopic[0]);
				}
			}
		});
	}, []);

	const [posts, setPosts] = useState([]);
	const [postData, setPostData] = useState([]);

	// fetch the projects
	useEffect(() => {
		setLoadingPosts(true);
		let thePath = "";
		console.log(undergradOnly);
		if(undergradOnly !== null){
			console.log("Showing undergrad only...");
			thePath = "/wp/v2/research-listing/?per_page=300&_fields=id,title,link,categories,topic,department,school,tags,image&tags_exclude=524";
		}else {
			console.log("Not showing undergrad only...");
			thePath = "/wp/v2/research-listing/?per_page=300&_fields=id,title,link,categories,topic,department,school,tags,image";
		}
		wp.api.loadPromise.done(function () {
			apiFetch({
				path: thePath,
			}).then((data) => {
				setPostData(data);
				setLoadingPosts(false);
			});
		});
	}, []);

	// filter the posts
	useEffect(() => {
		
		let p = postData;

		let selectedCategoryIds = selectedCategories.map((el) => el.id);
		if (selectedCategoryIds.length > 0) {
			p = p.filter((el) => {
				for (const topic of el.categories) {
					return selectedCategoryIds.includes(topic);
				}
			});
		}

		let selectedTagIds = selectedTags.map((el) => el.id);
		if (selectedTagIds.length > 0) {
			p = p.filter((el) => {
				let intersection = selectedTagIds.filter(element => el.tags.includes(element));
				return intersection.length > 0 ? true : false;
			});
		}

		let selectedTopicsIds = selectedTopics.map((el) => el.id);
		if (selectedTopicsIds.length > 0) {
			p = p.filter((el) => {
					let intersection = selectedTopicsIds.filter(element => el.topic.includes(element));
					return intersection.length > 0 ? true : false;
			}); 
		}

		let selectedSchoolIds = selectedSchools.map((el) => el.id);
		if (selectedSchoolIds.length > 0) {
			p = p.filter((el) => {
				for (const topic of el.school) {
					return selectedSchoolIds.includes(topic);
				}
			});
		}

		let selectedDepartmentIds = selectedDepartments.map((el) => parseInt(el.id));
		console.log(selectedDepartmentIds);
		console.log(p);
		if (selectedDepartmentIds.length > 0) {
			p = p.filter((el) => {
				let intersection = selectedDepartmentIds.filter(element => el.department.includes(element));
				return intersection.length > 0 ? true : false;
			});
		}

		setTitle(getTitle());
		setPosts(p);
	}, [
		postData,
		selectedTopics,
		selectedTags,
		selectedCategories,
		selectedSchools,
		selectedDepartments,
	]);

	// construct the section title
	const [title, setTitle] = useState("All Projects");
	function getTitle() {
		let schools = selectedSchools.map((school) => school.name).join(", ");
		if (schools !== "") return schools + " Projects";
		return "All Projects";
	}

	const resetCategories = () => {
		setSelectedCategories([]);
	};
	const resetTopics = () => {
		setSelectedTopics([]);
	};
	const resetTags = () => {
		setSelectedTags([]);
	};
	const resetSchools = () => {
		setSelectedSchools([]);
	};
	const resetDepartments = () => {
		setSelectedDepartments([]);
	};

	return (
		<div className="wrapper align-wide">
			{posts && categories && topics && departments && schools ? (
				<>
					<div className="sidebar">
						{categories && (
							<div className="tags-selector">
								<label
									for="headlessui-listbox-button-:r2:"
									className="functions-title"
								>
									Categories
								</label>
								<div onClick={resetCategories} className="reset">
									Reset
								</div>
								<Listbox
									value={selectedCategories}
									onChange={setSelectedCategories}
									multiple
								>
									<Listbox.Button>
										{selectedCategories.length > 0
											? selectedCategories.map((topic) => topic.name).join(", ")
											: "All Categories:"}
										<ChevronUpDownIcon className="button-icon" />
									</Listbox.Button>
									<Transition
										as={Fragment}
										leave="transition ease-in duration-100"
										leaveFrom="opacity-100"
										leaveTo="opacity-0"
									>
										<Listbox.Options>
											{categories.map((topic, i) => (
												<Listbox.Option
													key={i}
													value={topic}
													className={({ active }) =>
														`${active ? "active" : ""}`
													}
												>
													{({ selected }) => (
														<div
															className={
																selected ? "tags-item selected" : "tags-item"
															}
														>
															{selected ? (
																<CheckIcon
																	className="check-icon"
																	aria-hidden="true"
																/>
															) : null}
															{topic.name} ({topic.count})
														</div>
													)}
												</Listbox.Option>
											))}
										</Listbox.Options>
									</Transition>
								</Listbox>
							</div>
						)}
						{topics && (
							<div className="tags-selector">
								<label
									for="headlessui-listbox-button-:r2:"
									className="functions-title"
								>
									Topics
								</label>
								<div onClick={resetTopics} className="reset">
									Reset
								</div>
								<Listbox
									value={selectedTopics}
									onChange={setSelectedTopics}
									multiple
								>
									<Listbox.Button>
										{selectedTopics.length > 0
											? selectedTopics.map((topic) => topic.name).join(", ")
											: "All Topics:"}
										<ChevronUpDownIcon className="button-icon" />
									</Listbox.Button>
									<Transition
										as={Fragment}
										leave="transition ease-in duration-100"
										leaveFrom="opacity-100"
										leaveTo="opacity-0"
									>
										<Listbox.Options>
											{topics.map((topic, i) => (
												<Listbox.Option
													key={i}
													value={topic}
													className={({ active }) =>
														`${active ? "active" : ""}`
													}
												>
													{({ selected }) => (
														<div
															className={
																selected ? "tags-item selected" : "tags-item"
															}
														>
															{selected ? (
																<CheckIcon
																	className="check-icon"
																	aria-hidden="true"
																/>
															) : null}
															{topic.name} ({topic.count})
														</div>
													)}
												</Listbox.Option>
											))}
										</Listbox.Options>
									</Transition>
								</Listbox>
							</div>
						)}
						{(tags && qSchool !== null) && (
							<div className="tags-selector">
								<label
									for="headlessui-listbox-button-:r2:"
									className="functions-title"
								>
									Tags
								</label>
								<div onClick={resetTags} className="reset">
									Reset
								</div>
								<Listbox
									value={selectedTags}
									onChange={setSelectedTags}
									multiple
								>
									<Listbox.Button>
										{selectedTags.length > 0
											? selectedTags.map((topic) => topic.name).join(", ")
											: "All Tags:"}
										<ChevronUpDownIcon className="button-icon" />
									</Listbox.Button>
									<Transition
										as={Fragment}
										leave="transition ease-in duration-100"
										leaveFrom="opacity-100"
										leaveTo="opacity-0"
									>
										<Listbox.Options>
											{tags.map((topic, i) => (
												<Listbox.Option
													key={i}
													value={topic}
													className={({ active }) =>
														`${active ? "active" : ""}`
													}
												>
													{({ selected }) => (
														<div
															className={
																selected ? "tags-item selected" : "tags-item"
															}
														>
															{selected ? (
																<CheckIcon
																	className="check-icon"
																	aria-hidden="true"
																/>
															) : null}
															{topic.name} ({topic.count})
														</div>
													)}
												</Listbox.Option>
											))}
										</Listbox.Options>
									</Transition>
								</Listbox>
							</div>
						)}
						{departments && (
							<div className="tags-selector">
								<label
									for="headlessui-listbox-button-:r2:"
									className="functions-title"
								>
									Department
								</label>
								<div onClick={resetDepartments} className="reset">
									Reset
								</div>
								<Listbox
									value={selectedDepartments}
									onChange={setSelectedDepartments}
									multiple
								>
									<Listbox.Button>
										{selectedDepartments.length > 0
											? selectedDepartments
													.map((topic) => topic.name)
													.join(", ")
											: "All Departments:"}
										<ChevronUpDownIcon className="button-icon" />
									</Listbox.Button>
									<Transition
										as={Fragment}
										leave="transition ease-in duration-100"
										leaveFrom="opacity-100"
										leaveTo="opacity-0"
									>
										<Listbox.Options>
											{departments.map((topic, i) => (
												<Listbox.Option
													key={i}
													value={topic}
													className={({ active }) =>
														`${active ? "active" : ""}`
													}
												>
													{({ selected }) => (
														<div
															className={
																selected ? "tags-item selected" : "tags-item"
															}
														>
															{selected ? (
																<CheckIcon
																	className="check-icon"
																	aria-hidden="true"
																/>
															) : null}
															{topic.name} ({topic.count})
														</div>
													)}
												</Listbox.Option>
											))}
										</Listbox.Options>
									</Transition>
								</Listbox>
							</div>
						)}
						{schools && (
							<div className="tags-selector">
								<label
									for="headlessui-listbox-button-:r2:"
									className="functions-title"
								>
									School
								</label>
								<div onClick={resetSchools} className="reset">
									Reset
								</div>
								<Listbox
									value={selectedSchools}
									onChange={setSelectedSchools}
									multiple
								>
									<Listbox.Button>
										{selectedSchools.length > 0
											? selectedSchools.map((topic) => topic.name).join(", ")
											: "All Schools:"}
										<ChevronUpDownIcon className="button-icon" />
									</Listbox.Button>
									<Transition
										as={Fragment}
										leave="transition ease-in duration-100"
										leaveFrom="opacity-100"
										leaveTo="opacity-0"
									>
										<Listbox.Options>
											{schools.map((topic, i) => (
												<Listbox.Option
													key={i}
													value={topic}
													className={({ active }) =>
														`${active ? "active" : ""}`
													}
												>
													{({ selected }) => (
														<div
															className={
																selected ? "tags-item selected" : "tags-item"
															}
														>
															{selected ? (
																<CheckIcon
																	className="check-icon"
																	aria-hidden="true"
																/>
															) : null}
															{topic.name} ({topic.count})
														</div>
													)}
												</Listbox.Option>
											))}
										</Listbox.Options>
									</Transition>
								</Listbox>
							</div>
						)}
					</div>
				</>
			) : (
				"Loading..."
			)}
			<div className="main">
				<h3>{title}</h3>
				{!loadingPosts ? (
					<>
						{posts && posts.length === 0 && "No Projects Found."}
						{posts && (
							<ul className="posts-list">
								{posts.map((post) => {
									return (
										<li className="post">
											<a href={post.link}>
												<div className="post-inner">
													<div className="post-image-wrapper">
														{post.image.image_url ? (
															<img
																src={post.image.image_url}
																className="post-image"
															/>
														) : (
															/* fallback for no project image */
															<img
																src={window.location.origin+"/wp-content/themes/tu_research-theme/images/login-logo.png"}
																className="post-image-default"
															/>
														)}
													</div>

													<div className="post-title">
														{post.title.rendered}
													</div>
													<div className="learn-more">Learn More →</div>
												</div>
											</a>
										</li>
									);
								})}
							</ul>
						)}
					</>
				) : (
					<div className="spinner-wrap">
						<div class="lds-ring">
							<div></div>
							<div></div>
							<div></div>
							<div></div>
						</div>
					</div>
				)}
			</div>
		</div>
	);
}
