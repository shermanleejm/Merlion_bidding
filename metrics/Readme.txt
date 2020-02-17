1. How to compute the bug metric?
•It is computed after any bug is added to the bug log and mitigations (if needed) are performed on the newly computed bug metric (**NEW**)
•This is computed from all the bugs found and recorded in the bug log during testing during the iteration
•Bug found and fixed during PP sessions should not be included in the bug log. Only include bugs that are not fixed or are found in other functionality (that you were not working on during the PP session).
•You should schedule a task at the end of every iteration to compute and update the metric

2. Which bugs should we include in the bug metric?
•You should include all bugs found during testing of both your user interface (that you access via the Chrome browser) and your JSON APIs (that is tested using the json checker).
•You should create extensive and useful testcases for both the UI and the JSON APIs as you create the required functionalities
•Your testcases should thus increase every iteration -- as you add new functionality
•At the end of every iteration, you must schedule testing sessions that first create comprehensive test cases for all new functionality created that iteration and then runs all your test cases (for both the UI and the JSON APIs) again. This is called regression testing (running all testcases every iteration).
•You should not assume that previously tested functionality is still working.
