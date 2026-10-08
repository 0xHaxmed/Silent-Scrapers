import java.util.Scanner;

public class Text {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        boolean isNegative = false;

        System.out.print("Enter an integer number: ");
        int number = scanner.nextInt();

        if (number < 0) {
            isNegative = true;
        }

        if (isNegative) {
            System.out.println("Negative number");
        } else {
            System.out.println("Valid number");
        }

        scanner.close();
    }
}