package pt.ipleiria.estg.dei.amsi.cinelive;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.SearchView;
import androidx.fragment.app.Fragment;

import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentFilmesBinding;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link FilmesFragment#newInstance} factory method to
 * create an instance of this fragment.
 */
public class FilmesFragment extends Fragment {
    private FragmentFilmesBinding binding;
    private SearchView searchView;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentFilmesBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);
    }

    @Override
    public void onCreateOptionsMenu(@NonNull Menu menu, @NonNull MenuInflater inflater) {
        super.onCreateOptionsMenu(menu, inflater);

        inflater.inflate(R.menu.menu_pesquisa, menu);

        // Obter item
        MenuItem itemPesquisa = menu.findItem(R.id.itemPesquisa);

        // Obter SearchView
        searchView = (SearchView) itemPesquisa.getActionView();
        searchView.setQueryHint(getString(R.string.pesquisar_filmes));
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        binding.textTitle.setText("Filmes");
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}